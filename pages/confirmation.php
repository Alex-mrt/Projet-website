<?php
// On récupère ce que l'élève a rempli dans le formulaire
$creneau = trouverCreneau($creneaux, $_POST['id'] ?? '');

$nom        = trim($_POST['nom'] ?? '');
$prenom     = trim($_POST['prenom'] ?? '');
$classe     = trim($_POST['classe'] ?? '');
$instrument = trim($_POST['instrument'] ?? '');
?>

<?php if ($creneau === null || $nom === '' || $prenom === '' || $classe === ''): ?>

    <h1>Oups</h1>
    <p>Les informations sont incomplètes. Merci de recommencer ton inscription.</p>
    <a class="btn" href="index.php?page=home">Retour à l'accueil</a>

<?php else: ?>

    <h1>Inscription reçue ✅</h1>
    <p>Voici le récapitulatif de ton inscription.</p>

    <div class="carte">
        <div>
            <p><strong>Élève :</strong> <?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></p>
            <p><strong>Classe :</strong> <?= htmlspecialchars($classe) ?></p>
            <p><strong>Type :</strong> <?= $creneau['type'] === 'cours' ? 'Cours en groupe' : 'Pratique libre' ?></p>
            <?php if ($instrument !== ''): ?>
                <p><strong>Instrument :</strong> <?= htmlspecialchars($instrument) ?></p>
            <?php endif; ?>
            <p><strong>Date :</strong> <?= dateEnFrancais($creneau['date']) ?> à <?= $creneau['heure'] ?></p>
            <p><strong>Organisateur :</strong> <?= htmlspecialchars($creneau['organisateur']) ?></p>
        </div>
    </div>

    <a class="btn" href="index.php?page=<?= $creneau['type'] ?>">Retour aux créneaux</a>

<?php endif; ?>