<?php
$id = (string) ($_GET['id'] ?? '');
$creneau = trouverCreneau($creneaux, $id);
?>

<?php if ($creneau === null): ?>

    <h1>Créneau introuvable</h1>
    <p>Ce créneau n&apos;existe pas ou est déjà passé.</p>
    <a class="btn" href="index.php?page=planning">Retour au planning</a>

<?php elseif ($creneau['places'] - $creneau['inscrits'] <= 0): ?>

    <h1>Créneau complet</h1>
    <p>Il n&apos;y a malheureusement plus de place sur ce créneau.</p>
    <a class="btn" href="index.php?page=<?= e($creneau['type']) ?>">Retour aux créneaux</a>

<?php else: ?>

    <h1>Inscription</h1>
    <p>Inscrit(e) en tant que <strong><?= e($utilisateur['prenom']) ?> <?= e($utilisateur['nom']) ?></strong>.</p>

    <div class="message">
        <strong><?= $creneau['type'] === 'cours' ? 'Cours en groupe' : 'Pratique libre' ?></strong><br>
        <?= dateEnFrancais($creneau['date']) ?> à <?= e($creneau['heure']) ?><br>
        Organisateur : <?= e($creneau['organisateur']) ?>
    </div>

    <form method="post" action="index.php?page=inscription&amp;id=<?= urlencode($creneau['id']) ?>">
        <?= champCsrf() ?>
        <input type="hidden" name="action" value="reserver">
        <input type="hidden" name="id" value="<?= e($creneau['id']) ?>">

        <label for="classe">Classe</label>
        <input type="text" id="classe" name="classe" placeholder="ex : 3e B" maxlength="40" required>

        <?php if ($creneau['type'] === 'pratique'): ?>
            <label for="instrument">Instrument</label>
            <select id="instrument" name="instrument" required>
                <option value="">-- Choisis un instrument --</option>
                <?php foreach ($instruments as $i): ?>
                    <option value="<?= e($i) ?>"><?= e($i) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <button type="submit" class="btn btn-plein">Valider mon inscription</button>
    </form>

<?php endif; ?>
