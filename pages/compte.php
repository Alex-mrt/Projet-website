<?php
$mesReservations = reservationsUtilisateur((int) $utilisateur['id']);
$initiales = mb_strtoupper(mb_substr($utilisateur['prenom'], 0, 1) . mb_substr($utilisateur['nom'], 0, 1));
?>

<section class="compte-entete reveal">
    <div class="avatar" aria-hidden="true"><?= e($initiales) ?></div>
    <div>
        <h1><?= e($utilisateur['prenom']) ?> <?= e($utilisateur['nom']) ?></h1>
        <p><?= e($utilisateur['email']) ?></p>
    </div>
    <form method="post" action="index.php?page=compte" class="form-inline">
        <?= champCsrf() ?>
        <input type="hidden" name="action" value="deconnexion">
        <button type="submit" class="btn btn-secondaire">Se déconnecter</button>
    </form>
</section>

<h2 class="mois-titre">Mes réservations</h2>

<?php if (!$mesReservations): ?>
    <div class="message">Tu n&apos;as encore aucune réservation. <a href="index.php?page=planning">Voir le planning</a></div>
<?php endif; ?>

<?php foreach ($mesReservations as $r): ?>
    <?php $c = trouverCreneau($creneaux, $r['creneau_id']); ?>
    <?php if ($c === null) continue; ?>
    <div class="carte carte-creneau reveal" data-type="<?= e($c['type']) ?>">
        <div class="date-bloc">
            <span class="jour"><?= (int) substr($c['date'], 8, 2) ?></span>
            <span class="mois"><?= e(['', 'janv', 'févr', 'mars', 'avr', 'mai', 'juin', 'juil', 'août', 'sept', 'oct', 'nov', 'déc'][(int) substr($c['date'], 5, 2)]) ?></span>
        </div>
        <div class="infos">
            <span class="tag tag-<?= e($c['type']) ?>"><?= $c['type'] === 'cours' ? 'Cours en groupe' : 'Pratique libre' ?></span>
            <h3><?= dateEnFrancais($c['date']) ?> à <?= e($c['heure']) ?></h3>
            <p>Classe : <strong><?= e($r['classe']) ?></strong><?= $r['instrument'] ? ' · ' . e($r['instrument']) : '' ?></p>
        </div>
        <form method="post" action="index.php?page=compte" class="form-inline" data-confirmer="Annuler cette réservation ?">
            <?= champCsrf() ?>
            <input type="hidden" name="action" value="annuler">
            <input type="hidden" name="id" value="<?= e($r['creneau_id']) ?>">
            <button type="submit" class="btn btn-secondaire">Annuler</button>
        </form>
    </div>
<?php endforeach; ?>
