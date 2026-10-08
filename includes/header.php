<?php
$liens = [
    'home'     => 'Accueil',
    'planning' => 'Planning',
    'cours'    => 'Cours en groupe',
    'pratique' => 'Pratique libre',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="Bureau des Arts : réserve ta place aux cours en groupe et aux séances de pratique libre.">
    <title>Bureau des Arts — Réservations</title>
    <link rel="icon" href="assets/img/logo-bda.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js" defer></script>
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="index.php?page=home" class="logo" aria-label="Bureau des Arts, accueil">
            <img src="assets/img/logo-bda.png" alt="" width="120" height="80">
            <span>Bureau des Arts</span>
        </a>

        <button class="burger" type="button" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="menu">
            <span></span><span></span>
        </button>

        <nav id="menu" aria-label="Navigation principale">
            <?php foreach ($liens as $cle => $texte): ?>
                <a href="index.php?page=<?= $cle ?>"<?= $page === $cle ? ' class="actif" aria-current="page"' : '' ?>><?= $texte ?></a>
            <?php endforeach; ?>

            <?php if (!empty($utilisateur)): ?>
                <a href="index.php?page=compte" class="nav-compte<?= $page === 'compte' ? ' actif' : '' ?>"<?= $page === 'compte' ? ' aria-current="page"' : '' ?>>
                    <span class="mini-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($utilisateur['prenom'], 0, 1))) ?></span>
                    Mon compte
                </a>
            <?php else: ?>
                <a href="index.php?page=connexion" class="nav-connexion">Se connecter</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>
<?php if (!empty($flashs)): ?>
    <div class="flashs" role="status">
        <?php foreach ($flashs as $f): ?>
            <div class="flash flash-<?= e($f['type']) ?>"><?= $f['message'] ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
