<?php
// =====================================================================
//  CONTRÔLEURS : sécurité (session, CSRF, tokens) + actions des formulaires
// =====================================================================

const COOKIE_TOKEN = 'bda_token';

// ---------- OUTILS GÉNÉRAUX ----------

function estHttps(): bool {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function e(?string $texte): string {
    return htmlspecialchars((string) $texte, ENT_QUOTES, 'UTF-8');
}

function rediriger(string $url): never {
    header('Location: ' . $url, true, 303);
    exit;
}

function ipClient(): string {
    return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
}

function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function lireFlashs(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function garderSaisie(array $champs): void {
    $_SESSION['saisie'] = $champs;
}

function ancienneSaisie(string $champ): string {
    return e($_SESSION['saisie'][$champ] ?? '');
}

// ---------- SESSION & EN-TÊTES DE SÉCURITÉ ----------

function demarrerSession(): void {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('bda_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => estHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function envoyerEntetesSecurite(): void {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (estHttps()) {
        header('Strict-Transport-Security: max-age=63072000');
    }
}

// ---------- CSRF ----------

function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function champCsrf(): string {
    return '<input type="hidden" name="csrf" value="' . csrfToken() . '">';
}

function verifierCsrf(): void {
    $envoye = $_POST['csrf'] ?? '';
    if (!is_string($envoye) || !hash_equals(csrfToken(), $envoye)) {
        http_response_code(400);
        flash('erreur', 'Ta session a expiré, merci de réessayer.');
        rediriger('index.php?page=home');
    }
}

// ---------- AUTHENTIFICATION ----------

function poserCookieToken(string $selecteur, string $validateur): void {
    setcookie(COOKIE_TOKEN, $selecteur . ':' . $validateur, [
        'expires'  => time() + DUREE_TOKEN_JOURS * 86400,
        'path'     => '/',
        'secure'   => estHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function effacerCookieToken(): void {
    setcookie(COOKIE_TOKEN, '', ['expires' => time() - 3600, 'path' => '/', 'secure' => estHttps(), 'httponly' => true, 'samesite' => 'Lax']);
}

// Lit le cookie "selecteur:validateur" et renvoie le token BDD s'il est valide.
function tokenDepuisCookie(): ?array {
    $cookie = $_COOKIE[COOKIE_TOKEN] ?? '';
    if (!is_string($cookie) || !preg_match('/^([a-f0-9]{24}):([a-f0-9]{64})$/', $cookie, $m)) {
        return null;
    }
    $token = trouverTokenConnexion($m[1]);
    if (!$token || !hash_equals($token['validateur_hash'], hash('sha256', $m[2]))) {
        return null;
    }
    if ($token['expire_le'] < maintenant()) {
        supprimerTokenConnexion($m[1]);
        return null;
    }
    $token['validateur'] = $m[2];
    return $token;
}

// Prolonge le token de 30 jours (en BDD + cookie). Fait au plus une fois par heure.
function prolongerConnexion(array $token): void {
    if (($_SESSION['prolonge_le'] ?? 0) > time() - 3600) {
        return;
    }
    prolongerTokenConnexion((int) $token['id']);
    poserCookieToken($token['selecteur'], $token['validateur']);
    $_SESSION['prolonge_le'] = time();
}

function utilisateurCourant(): ?array {
    static $utilisateur = false;
    if ($utilisateur !== false) {
        return $utilisateur;
    }
    $utilisateur = null;

    $aUnCookie = isset($_COOKIE[COOKIE_TOKEN]);
    if (empty($_SESSION['uid']) && !$aUnCookie) {
        return null;
    }

    $token = $aUnCookie ? tokenDepuisCookie() : null;

    if ($token === null) {
        // Pas de token valide : la connexion n'est plus active.
        if ($aUnCookie) {
            effacerCookieToken();
        }
        unset($_SESSION['uid']);
        return null;
    }

    if (empty($_SESSION['uid']) || (int) $_SESSION['uid'] !== (int) $token['utilisateur_id']) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int) $token['utilisateur_id'];
        $_SESSION['selecteur'] = $token['selecteur'];
    }

    $u = utilisateurParId((int) $_SESSION['uid']);
    if (!$u || $u['verifie_le'] === null) {
        deconnecter();
        return null;
    }

    prolongerConnexion($token);
    $utilisateur = $u;
    return $u;
}

function connecter(array $u): void {
    session_regenerate_id(true);
    [$selecteur, $validateur] = creerTokenConnexion((int) $u['id']);
    poserCookieToken($selecteur, $validateur);
    $_SESSION['uid'] = (int) $u['id'];
    $_SESSION['selecteur'] = $selecteur;
    $_SESSION['prolonge_le'] = time();
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    supprimerTokensExpires();
}

function deconnecter(): void {
    if (!empty($_SESSION['selecteur'])) {
        supprimerTokenConnexion($_SESSION['selecteur']);
    }
    effacerCookieToken();
    $_SESSION = [];
    session_regenerate_id(true);
}

function exigerConnexion(string $retour): array {
    $u = utilisateurCourant();
    if ($u === null) {
        flash('info', 'Connecte-toi avec ton adresse de l\'école pour continuer.');
        rediriger('index.php?page=connexion&retour=' . urlencode($retour));
    }
    return $u;
}

// N'accepte que des retours internes (évite les redirections vers un autre site).
function urlRetourSure(?string $retour): string {
    if (is_string($retour) && preg_match('/^index\.php\?page=[a-z\-]+(&[a-zA-Z0-9_=%:\-]*)?$/', $retour)) {
        return $retour;
    }
    return 'index.php?page=planning';
}

// ---------- VALIDATION ----------

function emailEcoleValide(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        && str_ends_with($email, '@' . DOMAINE_AUTORISE)
        && strlen($email) <= 190;
}

function texteValide(string $texte, int $max): bool {
    return $texte !== '' && mb_strlen($texte) <= $max;
}

// ---------- ENVOI DU MAIL DE CONFIRMATION ----------

function envoyerLienConfirmation(array $u): void {
    $token = creerTokenVerification((int) $u['id']);
    $lien = APP_URL . '/index.php?page=verifier&token=' . $token;

    $html = '<div style="font-family:-apple-system,Segoe UI,Helvetica,Arial,sans-serif;max-width:520px;margin:auto;padding:32px;color:#1d1d1f">'
          . '<h1 style="font-size:24px;margin:0 0 16px">Bienvenue au Bureau des Arts, ' . e($u['prenom']) . ' !</h1>'
          . '<p style="color:#6e6e73;line-height:1.6">Clique sur le bouton ci-dessous pour confirmer ton adresse et activer ton compte. Ce lien est valable ' . DUREE_LIEN_CONFIRMATION_HEURES . ' heures.</p>'
          . '<p style="margin:28px 0"><a href="' . e($lien) . '" style="background:#e8384f;color:#fff;text-decoration:none;padding:14px 26px;border-radius:999px;font-weight:600;display:inline-block">Confirmer mon adresse</a></p>'
          . '<p style="color:#6e6e73;font-size:13px">Si tu n\'es pas à l\'origine de cette inscription, ignore simplement cet e-mail.</p>'
          . '</div>';
    $texte = "Bienvenue au Bureau des Arts !\n\nConfirme ton adresse en ouvrant ce lien (valable " . DUREE_LIEN_CONFIRMATION_HEURES . " h) :\n$lien\n";

    $envoye = envoyerMail($u['email'], 'Confirme ton compte Bureau des Arts', $html, $texte);

    if (!$envoye && MODE_DEV) {
        // En développement seulement : affiche le lien si l'e-mail n'a pas pu partir.
        flash('info', 'Mode dev — l\'e-mail n\'a pas pu être envoyé (SMTP non configuré). Lien de confirmation : <a href="' . e($lien) . '">confirmer le compte</a>');
    }
}

// ---------- ACTIONS (formulaires POST) ----------

function traiterAction(string $action, array $creneaux, array $instruments): void {
    verifierCsrf();

    switch ($action) {

        case 'creer-compte':
            $email  = strtolower(trim((string) ($_POST['email'] ?? '')));
            $prenom = trim((string) ($_POST['prenom'] ?? ''));
            $nom    = trim((string) ($_POST['nom'] ?? ''));
            $mdp    = (string) ($_POST['mot_de_passe'] ?? '');
            $mdp2   = (string) ($_POST['mot_de_passe_confirmation'] ?? '');
            garderSaisie(['email' => $email, 'prenom' => $prenom, 'nom' => $nom]);

            $erreur = null;
            if (!texteValide($prenom, 80) || !texteValide($nom, 80)) {
                $erreur = 'Merci d\'indiquer ton prénom et ton nom.';
            } elseif (!emailEcoleValide($email)) {
                $erreur = 'Utilise ton adresse de l\'école (@' . DOMAINE_AUTORISE . ').';
            } elseif (strlen($mdp) < 8 || strlen($mdp) > 72) {
                $erreur = 'Ton mot de passe doit contenir entre 8 et 72 caractères.';
            } elseif (!hash_equals($mdp, $mdp2)) {
                $erreur = 'Les deux mots de passe ne correspondent pas.';
            }
            if ($erreur) {
                flash('erreur', $erreur);
                rediriger('index.php?page=creer-compte');
            }

            $existant = utilisateurParEmail($email);
            if ($existant === null) {
                $id = creerUtilisateur($email, $prenom, $nom, $mdp);
                envoyerLienConfirmation(['id' => $id, 'email' => $email, 'prenom' => $prenom]);
            } elseif ($existant['verifie_le'] === null) {
                envoyerLienConfirmation($existant);
            }
            // Même message dans tous les cas : on ne révèle pas si un compte existe déjà.
            unset($_SESSION['saisie']);
            $_SESSION['email_en_attente'] = $email;
            rediriger('index.php?page=verifier');

        case 'renvoyer':
            $email = strtolower(trim((string) ($_POST['email'] ?? ($_SESSION['email_en_attente'] ?? ''))));
            if (($_SESSION['dernier_renvoi'] ?? 0) > time() - 60) {
                flash('erreur', 'Patiente une minute avant de redemander un e-mail.');
                rediriger('index.php?page=verifier');
            }
            $_SESSION['dernier_renvoi'] = time();
            if (emailEcoleValide($email)) {
                $u = utilisateurParEmail($email);
                if ($u && $u['verifie_le'] === null) {
                    envoyerLienConfirmation($u);
                }
            }
            $_SESSION['email_en_attente'] = $email;
            flash('succes', 'Si un compte non confirmé existe pour cette adresse, un nouvel e-mail vient d\'être envoyé.');
            rediriger('index.php?page=verifier');

        case 'connexion':
            $email  = strtolower(trim((string) ($_POST['email'] ?? '')));
            $mdp    = (string) ($_POST['mot_de_passe'] ?? '');
            $retour = urlRetourSure($_POST['retour'] ?? null);
            garderSaisie(['email' => $email]);
            $pageConnexion = 'index.php?page=connexion&retour=' . urlencode($retour);

            if (tropDEssais($email, ipClient())) {
                flash('erreur', 'Trop de tentatives. Réessaie dans 15 minutes.');
                rediriger($pageConnexion);
            }

            $u = utilisateurParEmail($email);
            // Hash factice pour que le temps de réponse soit identique si le compte n'existe pas.
            $hash = $u['mot_de_passe'] ?? '$2y$10$abcdefghijklmnopqrstuuMB3uVxYbS2c2QJr2WjcQ6Fz4nG4QqXu';
            if (!password_verify($mdp, $hash) || $u === null) {
                noterEssaiRate($email, ipClient());
                flash('erreur', 'Adresse e-mail ou mot de passe incorrect.');
                rediriger($pageConnexion);
            }
            if ($u['verifie_le'] === null) {
                $_SESSION['email_en_attente'] = $email;
                flash('erreur', 'Ton adresse n\'est pas encore confirmée. Vérifie ta boîte mail.');
                rediriger('index.php?page=verifier');
            }
            if (password_needs_rehash($u['mot_de_passe'], PASSWORD_DEFAULT)) {
                majHashMotDePasse((int) $u['id'], $mdp);
            }
            effacerEssais($email);
            unset($_SESSION['saisie']);
            connecter($u);
            flash('succes', 'Content de te revoir, ' . e($u['prenom']) . ' !');
            rediriger($retour);

        case 'deconnexion':
            deconnecter();
            flash('succes', 'Tu es déconnecté(e). À bientôt !');
            rediriger('index.php?page=home');

        case 'reserver':
            $id = (string) ($_POST['id'] ?? '');
            $u = exigerConnexion('index.php?page=inscription&id=' . urlencode($id));
            $creneau = trouverCreneau($creneaux, $id);
            $classe = trim((string) ($_POST['classe'] ?? ''));
            $instrument = trim((string) ($_POST['instrument'] ?? ''));

            if ($creneau === null) {
                flash('erreur', 'Ce créneau n\'existe pas ou est déjà passé.');
                rediriger('index.php?page=planning');
            }
            $pageResa = 'index.php?page=inscription&id=' . urlencode($id);
            if (!texteValide($classe, 40)) {
                flash('erreur', 'Indique ta classe.');
                rediriger($pageResa);
            }
            if ($creneau['type'] === 'pratique' && !in_array($instrument, $instruments, true)) {
                flash('erreur', 'Choisis un instrument dans la liste.');
                rediriger($pageResa);
            }

            $resultat = reserverCreneau((int) $u['id'], $creneau, $classe, $creneau['type'] === 'pratique' ? $instrument : null);
            if ($resultat === 'complet') {
                flash('erreur', 'Désolé, ce créneau vient d\'être complété.');
                rediriger('index.php?page=' . $creneau['type']);
            }
            if ($resultat === 'deja') {
                flash('info', 'Tu es déjà inscrit(e) à ce créneau.');
            }
            rediriger('index.php?page=confirmation&id=' . urlencode($id));

        case 'annuler':
            $u = exigerConnexion('index.php?page=compte');
            annulerReservation((int) $u['id'], (string) ($_POST['id'] ?? ''));
            flash('succes', 'Ta réservation a bien été annulée.');
            rediriger('index.php?page=compte');
    }

    rediriger('index.php?page=home');
}

// Traitement du lien reçu par e-mail (?page=verifier&token=...)
function traiterVerification(string $token): void {
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        flash('erreur', 'Ce lien de confirmation est invalide.');
        rediriger('index.php?page=verifier');
    }
    $id = consommerTokenVerification($token);
    if ($id === null) {
        flash('erreur', 'Ce lien est invalide ou a expiré. Demande un nouvel e-mail ci-dessous.');
        rediriger('index.php?page=verifier');
    }
    $u = utilisateurParId($id);
    unset($_SESSION['email_en_attente']);
    connecter($u);
    flash('succes', 'Adresse confirmée ! Bienvenue, ' . e($u['prenom']) . '. Tu restes connecté(e) ' . DUREE_TOKEN_JOURS . ' jours.');
    rediriger('index.php?page=planning');
}
