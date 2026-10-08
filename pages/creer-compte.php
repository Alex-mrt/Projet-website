<section class="auth reveal">
    <div class="auth-entete">
        <img src="assets/img/logo-bda.png" alt="" width="96" height="64">
        <h1>Créer un compte</h1>
        <p>Réservé aux élèves avec une adresse <strong>@<?= e(DOMAINE_AUTORISE) ?></strong>.</p>
    </div>

    <form method="post" action="index.php?page=creer-compte" class="auth-form" novalidate>
        <?= champCsrf() ?>
        <input type="hidden" name="action" value="creer-compte">

        <div class="champs-duo">
            <div>
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="<?= ancienneSaisie('prenom') ?>" maxlength="80" autocomplete="given-name" required>
            </div>
            <div>
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="<?= ancienneSaisie('nom') ?>" maxlength="80" autocomplete="family-name" required>
            </div>
        </div>

        <label for="email">Adresse e-mail de l&apos;école</label>
        <input type="email" id="email" name="email" value="<?= ancienneSaisie('email') ?>"
               placeholder="prenom.nom@<?= e(DOMAINE_AUTORISE) ?>" autocomplete="email"
               data-domaine="<?= e(DOMAINE_AUTORISE) ?>" required>

        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="8" maxlength="72" autocomplete="new-password" required>
        <p class="aide">8 caractères minimum.</p>

        <label for="mot_de_passe_confirmation">Confirme le mot de passe</label>
        <input type="password" id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" minlength="8" maxlength="72" autocomplete="new-password" required>

        <button type="submit" class="btn btn-plein">Créer mon compte</button>
        <p class="auth-note">Un e-mail de confirmation te sera envoyé pour activer ton compte.</p>
    </form>

    <p class="auth-bascule">Déjà un compte ? <a href="index.php?page=connexion">Se connecter</a></p>
</section>
