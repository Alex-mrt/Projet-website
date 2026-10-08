<?php
// ============================================
//  FICHIER À MODIFIER POUR CHANGER LE PLANNING
// ============================================
//
// type   : 'cours' ou 'pratique'
// date   : format ANNEE-MOIS-JOUR (ex : 2026-11-05)
// heure  : format HH:MM (ex : 18:00), toujours 2 chiffres pour l'heure (09:00 et pas 9:00)
// places : nombre de places maximum

$creneaux_bruts = [

    // ----- OCTOBRE -----
    ['type' => 'cours',    'date' => '2026-10-06', 'heure' => '18:00', 'organisateur' => 'Marie',  'places' => 10],
    ['type' => 'pratique', 'date' => '2026-10-10', 'heure' => '14:00', 'organisateur' => 'Lucas',  'places' => 6],
    ['type' => 'cours',    'date' => '2026-10-15', 'heure' => '18:30', 'organisateur' => 'Karim',  'places' => 12],

    // ----- NOVEMBRE -----
    ['type' => 'pratique', 'date' => '2026-11-04', 'heure' => '17:00', 'organisateur' => 'Sophie', 'places' => 8],
    ['type' => 'cours',    'date' => '2026-11-12', 'heure' => '18:00', 'organisateur' => 'Marie',  'places' => 10],

    // ----- DÉCEMBRE -----
    ['type' => 'pratique', 'date' => '2026-12-02', 'heure' => '16:00', 'organisateur' => 'Lucas',  'places' => 6],

    // Ajoute tes créneaux ici ↑

];

// Liste des instruments proposés pour la pratique libre (modifie-la comme tu veux)
$instruments = ['Guitare', 'Basse', 'Piano', 'Batterie', 'Chant', 'Violon', 'Autre'];

// ---- Ne pas toucher en dessous ----

$creneaux = [];
$aujourdhui = date('Y-m-d');

foreach ($creneaux_bruts as $c) {
    // On cache automatiquement les créneaux déjà passés
    if ($c['date'] < $aujourdhui) {
        continue;
    }
    $c['id'] = $c['date'] . '_' . $c['heure'] . '_' . $c['type'];
    $c['inscrits'] = 0; // remplacé par le vrai nombre (base de données) dans index.php
    $creneaux[] = $c;
}

// Tri par date puis par heure
usort($creneaux, function ($a, $b) {
    return strcmp($a['date'] . $a['heure'], $b['date'] . $b['heure']);
});

// Transforme 2026-10-06 en "mardi 6 octobre 2026"
function dateEnFrancais($date) {
    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois  = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
              'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

    $t = strtotime($date);
    return $jours[date('w', $t)] . ' ' . date('j', $t) . ' ' . $mois[(int) date('n', $t)] . ' ' . date('Y', $t);
}

// Retrouve un créneau grâce à son id (renvoie null s'il n'existe pas)
function trouverCreneau($creneaux, $id) {
    foreach ($creneaux as $c) {
        if ($c['id'] === $id) {
            return $c;
        }
    }
    return null;
}
