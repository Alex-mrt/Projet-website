<section class="auth reveal">
    <div class="auth-entete">
        <img src="assets/img/logo-bda.png" alt="" width="96" height="64">
        <h1>Service momentanément indisponible</h1>
        <p>Impossible de joindre la base de données. Réessaie dans quelques instants.</p>
        <?php if (MODE_DEV): ?>
            <div class="message">
                <strong>Mode dev :</strong> renseigne tes identifiants MySQL dans <code>config/config.php</code>
                puis importe <code>database/schema.sql</code> dans ta base.
            </div>
        <?php endif; ?>
        <a class="btn" href="index.php?page=home">Retour à l&apos;accueil</a>
    </div>
</section>
