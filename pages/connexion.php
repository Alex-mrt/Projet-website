<?php $retour = urlRetourSure($_GET['retour'] ?? null); ?>

<section class="auth reveal">
    <div class="auth-entete">
        <img src="assets/img/logo-bda.png" alt="" width="96" height="64">
        <h1>Connexion</h1>
        <p>Accède aux réservations avec ton compte de l&apos;école.</p>
    </div>

    <form method="post" action="index.php?page=connexion" class="auth-form" novalidate>
        <?= champCsrf() ?>
        <input type="hidden" name="action" value="connexion">
        <input type="hidden" name="retour" value="<?= e($retour) ?>">

        <label for="email">Adresse e-mail de l&apos;école</label>
        <input type="email" id="email" name="email" value="<?= ancienneSaisie('email') ?>"
               placeholder="prenom.nom@<?= e(DOMAINE_AUTORISE) ?>" autocomplete="email" required>

        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="current-password" required>

        <button type="submit" class="btn btn-plein">Se connecter</button>

        <p class="auth-note">Tu resteras connecté(e) <?= DUREE_TOKEN_JOURS ?> jours, renouvelés à chaque visite.</p>
    </form>

    <p class="auth-bascule">Pas encore de compte ? <a href="index.php?page=creer-compte">Créer un compte</a></p>
</section>
