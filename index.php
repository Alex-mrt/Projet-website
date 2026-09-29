<?php
// Pour voir les erreurs pendant que tu construis le site (à retirer à la fin)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Liste des pages qui existent
$pages = ['home', 'planning', 'cours', 'pratique', 'inscription', 'confirmation'];

$page = $_GET['page'] ?? 'home';

if (!in_array($page, $pages)) {
    $page = 'home';
}

include 'data/creneaux.php';
include 'includes/header.php';
include "pages/$page.php";