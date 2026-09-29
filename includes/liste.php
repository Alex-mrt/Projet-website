<?php
// $type est défini dans la page qui appelle ce fichier :
//   'tous' / 'cours' / 'pratique'

$noms_mois = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
              'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
$mois_courts = ['', 'janv', 'févr', 'mars', 'avr', 'mai', 'juin',
                'juil', 'août', 'sept', 'oct', 'nov', 'déc'];

$mois_precedent = '';
$nb_affiches = 0;
?>

<?php foreach ($creneaux as $c): ?>
    <?php
    if ($type !== 'tous' && $c['type'] !== $type) {
        continue;
    }
    $nb_affiches++;

    // Titre de mois (affiché une seule fois par mois)
    $mois_actuel = substr($c['date'], 0, 7);
    if ($mois_actuel !== $mois_precedent) {
        echo '<h2 class="mois-titre">' . $noms_mois[(int) substr($c['date'], 5, 2)] . ' ' . substr($c['date'], 0, 4) . '</h2>';
        $mois_precedent = $mois_actuel;
    }

    // Jour et mois pour le bloc date
    $jour = (int) substr($c['date'], 8, 2);
    $mois_court = $mois_courts[(int) substr($c['date'], 5, 2)];

    // Places restantes
    $restantes = $c['places'] - $c['inscrits'];
    if ($restantes <= 0) {
        $classe_places = 'places-complet';
        $texte_places = 'Complet';
    } elseif ($restantes <= 3) {
        $classe_places = 'places-peu';
        $texte_places = $restantes . ' place(s) restante(s)';
    } else {
        $classe_places = 'places-ok';
        $texte_places = $restantes . ' places restantes';
    }
    ?>

    <div class="carte">

        <div class="date-bloc">
            <span class="jour"><?= $jour ?></span>
            <span class="mois"><?= $mois_court ?></span>
        </div>

        <div class="infos">
            <?php if ($type === 'tous'): ?>
                <span class="tag tag-<?= $c['type'] ?>"><?= $c['type'] === 'cours' ? 'Cours en groupe' : 'Pratique libre' ?></span>
            <?php endif; ?>
            <h3><?= dateEnFrancais($c['date']) ?> à <?= $c['heure'] ?></h3>
            <p>Organisateur : <strong><?= htmlspecialchars($c['organisateur']) ?></strong></p>
            <p class="<?= $classe_places ?>"><?= $texte_places ?></p>
        </div>

        <?php if ($restantes > 0): ?>
            <a class="btn" href="index.php?page=inscription&id=<?= urlencode($c['id']) ?>">S'inscrire</a>
        <?php else: ?>
            <span class="btn btn-gris">Complet</span>
        <?php endif; ?>

    </div>

<?php endforeach; ?>

<?php if ($nb_affiches === 0): ?>
    <div class="message">Aucun créneau disponible pour le moment.</div>
<?php endif; ?>