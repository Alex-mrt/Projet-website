<?php
// Liste des instruments proposés (modifie-la comme tu veux)
$instruments = ['Guitare', 'Basse', 'Piano', 'Batterie', 'Chant', 'Violon', 'Autre'];

// On retrouve le créneau choisi grâce à l'id dans l'adresse
$id = $_GET['id'] ?? '';
$creneau = trouverCreneau($creneaux, $id);
?>

<?php if ($creneau === null): ?>

    <h1>Créneau introuvable</h1>
    <p>Ce créneau n'existe pas ou est déjà passé.</p>
    <a class="btn" href="index.php?page=home">Retour à l'accueil</a>

<?php elseif ($creneau['places'] - $creneau['inscrits'] <= 0): ?>

    <h1>Créneau complet</h1>
    <p>Il n'y a malheureusement plus de place sur ce créneau.</p>
    <a class="btn" href="index.php?page=<?= $creneau['type'] ?>">Retour aux créneaux</a>

<?php else: ?>

    <h1>Inscription</h1>

    <div class="message">
        <strong><?= $creneau['type'] === 'cours' ? 'Cours en groupe' : 'Pratique libre' ?></strong><br>
        <?= dateEnFrancais($creneau['date']) ?> à <?= $creneau['heure'] ?><br>
        Organisateur : <?= htmlspecialchars($creneau['organisateur']) ?>
    </div>

    <form method="post" action="index.php?page=confirmation">

        <!-- Champ caché : permet de savoir à quel créneau l'élève s'inscrit -->
        <input type="hidden" name="id" value="<?= htmlspecialchars($creneau['id']) ?>">

        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" required>

        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" required>

        <label for="classe">Classe</label>
        <input type="text" id="classe" name="classe" placeholder="ex : 3e B" required>

        <?php if ($creneau['type'] === 'pratique'): ?>
            <label for="instrument">Instrument</label>
            <select id="instrument" name="instrument" required>
                <option value="">-- Choisis un instrument --</option>
                <?php foreach ($instruments as $i): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <button type="submit" class="btn btn-plein">Valider mon inscription</button>
    </form>

<?php endif; ?>
