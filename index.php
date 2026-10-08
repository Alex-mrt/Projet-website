<?php
require __DIR__ . '/config/config.php';

ini_set('display_errors', MODE_DEV ? '1' : '0');
error_reporting(E_ALL);

require __DIR__ . '/models/models.php';
require __DIR__ . '/controllers/mail.php';
require __DIR__ . '/controllers/controllers.php';
require __DIR__ . '/data/creneaux.php';

envoyerEntetesSecurite();
demarrerSession();

// Pages publiques / pages réservées aux élèves connectés
$pagesPubliques = ['home', 'connexion', 'creer-compte', 'verifier'];
$pagesPrivees   = ['planning', 'cours', 'pratique', 'inscription', 'confirmation', 'compte'];

$page = $_GET['page'] ?? 'home';
if (!is_string($page) || !in_array($page, array_merge($pagesPubliques, $pagesPrivees), true)) {
    $page = 'home';
}

try {
    // 1) Formulaires envoyés (POST) → action puis redirection
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        traiterAction((string) ($_POST['action'] ?? ''), $creneaux, $instruments);
    }

    // 2) Lien de confirmation reçu par e-mail
    if ($page === 'verifier' && isset($_GET['token'])) {
        traiterVerification((string) $_GET['token']);
    }

    // 3) Utilisateur connecté ? (prolonge automatiquement le token de 30 jours)
    $utilisateur = in_array($page, $pagesPrivees, true)
        ? exigerConnexion('index.php?' . http_build_query($_GET))
        : (isset($_COOKIE[COOKIE_TOKEN]) || !empty($_SESSION['uid']) ? utilisateurCourant() : null);

    if ($utilisateur && in_array($page, ['connexion', 'creer-compte'], true)) {
        rediriger('index.php?page=planning');
    }

    // 4) Places restantes réelles depuis la base
    if (in_array($page, $pagesPrivees, true)) {
        $inscrits = compterInscritsParCreneau();
        foreach ($creneaux as &$c) {
            $c['inscrits'] = $inscrits[$c['id']] ?? 0;
        }
        unset($c);
        header('Cache-Control: no-store');
    }
} catch (BaseIndisponible $e) {
    http_response_code(503);
    $utilisateur = null;
    $page = 'erreur-db';
}

$flashs = lireFlashs();

include 'includes/header.php';
include "pages/$page.php";
include 'includes/footer.php';

unset($_SESSION['saisie']);
