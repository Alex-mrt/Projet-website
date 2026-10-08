<?php $emailEnAttente = $_SESSION['email_en_attente'] ?? ''; ?>

<section class="auth reveal">
    <div class="auth-entete">
        <div class="icone icone-grande" aria-hidden="true">
            <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4 7 8 6 8-6"/></svg>
        </div>
        <h1>Vérifie ta boîte mail</h1>
        <p>
            <?php if ($emailEnAttente !== ''): ?>
                Un lien de confirmation a été envoyé à <strong><?= e($emailEnAttente) ?></strong>.
            <?php else: ?>
                Un lien de confirmation a été envoyé à ton adresse de l&apos;école.
            <?php endif; ?>
            Clique dessus pour activer ton compte.
        </p>
    </div>

    <form method="post" action="index.php?page=verifier" class="auth-form">
        <?= champCsrf() ?>
        <input type="hidden" name="action" value="renvoyer">
        <label for="email">Tu n&apos;as rien reçu ? Pense aux spams, ou renvoie le lien :</label>
        <input type="email" id="email" name="email" value="<?= e($emailEnAttente) ?>"
               placeholder="prenom.nom@<?= e(DOMAINE_AUTORISE) ?>" required>
        <button type="submit" class="btn btn-plein btn-secondaire">Renvoyer l&apos;e-mail</button>
    </form>

    <p class="auth-bascule"><a href="index.php?page=connexion">Retour à la connexion</a></p>
</section>
