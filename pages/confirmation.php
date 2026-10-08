<?php
$id = (string) ($_GET['id'] ?? '');
$creneau = trouverCreneau($creneaux, $id);
$reservation = null;
foreach (reservationsUtilisateur((int) $utilisateur['id']) as $r) {
    if ($r['creneau_id'] === $id) {
        $reservation = $r;
    }
}
?>

<?php if ($creneau === null || $reservation === null): ?>

    <h1>Aucune réservation</h1>
    <p>Nous n&apos;avons pas trouvé cette réservation.</p>
    <a class="btn" href="index.php?page=planning">Retour au planning</a>

<?php else: ?>

    <div class="succes-icone" aria-hidden="true">
        <svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="M15 27l7 7 15-16"/></svg>
    </div>
    <h1>Inscription confirmée</h1>
    <p>Ta place est réservée. Retrouve toutes tes réservations dans ton compte.</p>

    <div class="carte recap">
        <div>
            <p><strong>Élève :</strong> <?= e($utilisateur['prenom']) ?> <?= e($utilisateur['nom']) ?></p>
            <p><strong>Classe :</strong> <?= e($reservation['classe']) ?></p>
            <p><strong>Type :</strong> <?= $creneau['type'] === 'cours' ? 'Cours en groupe' : 'Pratique libre' ?></p>
            <?php if ($reservation['instrument']): ?>
                <p><strong>Instrument :</strong> <?= e($reservation['instrument']) ?></p>
            <?php endif; ?>
            <p><strong>Date :</strong> <?= dateEnFrancais($creneau['date']) ?> à <?= e($creneau['heure']) ?></p>
            <p><strong>Organisateur :</strong> <?= e($creneau['organisateur']) ?></p>
        </div>
    </div>

    <div class="hero-actions hero-actions-gauche">
        <a class="btn" href="index.php?page=compte">Mes réservations</a>
        <a class="btn btn-secondaire" href="index.php?page=<?= e($creneau['type']) ?>">Autres créneaux</a>
    </div>

<?php endif; ?>
