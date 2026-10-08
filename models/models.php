<?php
// =====================================================================
//  MODÈLES : toutes les requêtes SQL du site (requêtes préparées = pas
//  d'injection SQL possible)
// =====================================================================

class BaseIndisponible extends RuntimeException {}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (Throwable $e) {
        error_log('[BDA] Connexion MySQL impossible : ' . $e->getMessage());
        throw new BaseIndisponible('Base de données indisponible.');
    }
    return $pdo;
}

function maintenant(): string {
    return date('Y-m-d H:i:s');
}

// ---------- UTILISATEURS ----------

function utilisateurParEmail(string $email): ?array {
    $q = db()->prepare('SELECT * FROM utilisateurs WHERE email = ?');
    $q->execute([$email]);
    return $q->fetch() ?: null;
}

function utilisateurParId(int $id): ?array {
    $q = db()->prepare('SELECT id, email, prenom, nom, verifie_le FROM utilisateurs WHERE id = ?');
    $q->execute([$id]);
    return $q->fetch() ?: null;
}

function creerUtilisateur(string $email, string $prenom, string $nom, string $motDePasse): int {
    $q = db()->prepare('INSERT INTO utilisateurs (email, prenom, nom, mot_de_passe) VALUES (?, ?, ?, ?)');
    $q->execute([$email, $prenom, $nom, password_hash($motDePasse, PASSWORD_DEFAULT)]);
    return (int) db()->lastInsertId();
}

function majHashMotDePasse(int $id, string $motDePasse): void {
    $q = db()->prepare('UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?');
    $q->execute([password_hash($motDePasse, PASSWORD_DEFAULT), $id]);
}

// ---------- VÉRIFICATION E-MAIL ----------

function creerTokenVerification(int $utilisateurId): string {
    db()->prepare('DELETE FROM verifications_email WHERE utilisateur_id = ?')->execute([$utilisateurId]);

    $token = bin2hex(random_bytes(32));
    $expire = date('Y-m-d H:i:s', time() + DUREE_LIEN_CONFIRMATION_HEURES * 3600);
    $q = db()->prepare('INSERT INTO verifications_email (utilisateur_id, token_hash, expire_le) VALUES (?, ?, ?)');
    $q->execute([$utilisateurId, hash('sha256', $token), $expire]);
    return $token;
}

// Renvoie l'id de l'utilisateur vérifié, ou null si le lien est invalide/expiré.
function consommerTokenVerification(string $token): ?int {
    $q = db()->prepare('SELECT id, utilisateur_id, expire_le FROM verifications_email WHERE token_hash = ?');
    $q->execute([hash('sha256', $token)]);
    $ligne = $q->fetch();
    if (!$ligne) {
        return null;
    }
    db()->prepare('DELETE FROM verifications_email WHERE id = ?')->execute([$ligne['id']]);
    if ($ligne['expire_le'] < maintenant()) {
        return null;
    }
    db()->prepare('UPDATE utilisateurs SET verifie_le = ? WHERE id = ? AND verifie_le IS NULL')
        ->execute([maintenant(), $ligne['utilisateur_id']]);
    return (int) $ligne['utilisateur_id'];
}

// ---------- TOKENS DE CONNEXION (30 jours glissants) ----------

function creerTokenConnexion(int $utilisateurId): array {
    $selecteur  = bin2hex(random_bytes(12));
    $validateur = bin2hex(random_bytes(32));
    $expire = date('Y-m-d H:i:s', time() + DUREE_TOKEN_JOURS * 86400);
    $q = db()->prepare('INSERT INTO tokens_connexion (utilisateur_id, selecteur, validateur_hash, expire_le) VALUES (?, ?, ?, ?)');
    $q->execute([$utilisateurId, $selecteur, hash('sha256', $validateur), $expire]);
    return [$selecteur, $validateur];
}

function trouverTokenConnexion(string $selecteur): ?array {
    $q = db()->prepare('SELECT * FROM tokens_connexion WHERE selecteur = ?');
    $q->execute([$selecteur]);
    return $q->fetch() ?: null;
}

function prolongerTokenConnexion(int $tokenId): void {
    $expire = date('Y-m-d H:i:s', time() + DUREE_TOKEN_JOURS * 86400);
    db()->prepare('UPDATE tokens_connexion SET expire_le = ? WHERE id = ?')->execute([$expire, $tokenId]);
}

function supprimerTokenConnexion(string $selecteur): void {
    db()->prepare('DELETE FROM tokens_connexion WHERE selecteur = ?')->execute([$selecteur]);
}

function supprimerTokensExpires(): void {
    db()->prepare('DELETE FROM tokens_connexion WHERE expire_le < ?')->execute([maintenant()]);
}

// ---------- ANTI-BRUTEFORCE ----------

function tropDEssais(string $email, string $ip): bool {
    $depuis = date('Y-m-d H:i:s', time() - 15 * 60);
    $q = db()->prepare('SELECT COUNT(*) FROM essais_connexion WHERE (email = ? OR ip = ?) AND essaye_le > ?');
    $q->execute([$email, $ip, $depuis]);
    return (int) $q->fetchColumn() >= MAX_ESSAIS_CONNEXION;
}

function noterEssaiRate(string $email, string $ip): void {
    db()->prepare('INSERT INTO essais_connexion (email, ip) VALUES (?, ?)')->execute([$email, $ip]);
}

function effacerEssais(string $email): void {
    db()->prepare('DELETE FROM essais_connexion WHERE email = ?')->execute([$email]);
}

// ---------- RÉSERVATIONS ----------

function compterInscritsParCreneau(): array {
    $resultat = [];
    foreach (db()->query('SELECT creneau_id, COUNT(*) AS n FROM reservations GROUP BY creneau_id') as $l) {
        $resultat[$l['creneau_id']] = (int) $l['n'];
    }
    return $resultat;
}

// Réserve de façon atomique : verrouille le créneau pour éviter le surbooking.
// Renvoie 'ok', 'complet' ou 'deja'.
function reserverCreneau(int $utilisateurId, array $creneau, string $classe, ?string $instrument): string {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $verrou = $pdo->prepare('SELECT GET_LOCK(?, 5)');
        $verrou->execute(['resa_' . $creneau['id']]);

        $q = $pdo->prepare('SELECT COUNT(*) FROM reservations WHERE creneau_id = ?');
        $q->execute([$creneau['id']]);
        if ((int) $q->fetchColumn() >= $creneau['places']) {
            $resultat = 'complet';
        } else {
            $ins = $pdo->prepare('INSERT IGNORE INTO reservations (utilisateur_id, creneau_id, classe, instrument) VALUES (?, ?, ?, ?)');
            $ins->execute([$utilisateurId, $creneau['id'], $classe, $instrument]);
            $resultat = $ins->rowCount() === 1 ? 'ok' : 'deja';
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    } finally {
        $pdo->prepare('SELECT RELEASE_LOCK(?)')->execute(['resa_' . $creneau['id']]);
    }
    return $resultat;
}

function reservationsUtilisateur(int $utilisateurId): array {
    $q = db()->prepare('SELECT * FROM reservations WHERE utilisateur_id = ? ORDER BY creneau_id');
    $q->execute([$utilisateurId]);
    return $q->fetchAll();
}

function annulerReservation(int $utilisateurId, string $creneauId): void {
    db()->prepare('DELETE FROM reservations WHERE utilisateur_id = ? AND creneau_id = ?')->execute([$utilisateurId, $creneauId]);
}
