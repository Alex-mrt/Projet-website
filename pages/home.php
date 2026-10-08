<?php
$nb_cours = 0;
$nb_pratique = 0;
foreach ($creneaux as $c) {
    if ($c['type'] === 'cours') {
        $nb_cours++;
    } else {
        $nb_pratique++;
    }
}
$prochain = $creneaux[0] ?? null;
?>

<section class="hero reveal">
    <span class="hero-badge"><span class="point"></span> Inscriptions ouvertes</span>
    <h1>Crée. Joue.<br><span class="degrade">Partage.</span></h1>
    <p>Réserve ta place aux cours en groupe et aux séances de pratique libre du Bureau des Arts.</p>
    <div class="hero-actions">
        <a class="btn" href="index.php?page=planning">Voir le planning</a>
        <a class="btn btn-secondaire" href="#decouvrir">Découvrir</a>
    </div>

    <dl class="stats">
        <div>
            <dt>Cours à venir</dt>
            <dd data-compteur="<?= $nb_cours ?>"><?= $nb_cours ?></dd>
        </div>
        <div>
            <dt>Pratiques libres</dt>
            <dd data-compteur="<?= $nb_pratique ?>"><?= $nb_pratique ?></dd>
        </div>
        <div>
            <dt>Prochain créneau</dt>
            <dd class="stat-texte"><?= $prochain ? date('d/m', strtotime($prochain['date'])) . ' · ' . $prochain['heure'] : '—' ?></dd>
        </div>
    </dl>
</section>

<div class="message reveal">
    Les créneaux sont ouverts environ 1 mois à l'avance. Pense à t'inscrire tôt, les places sont limitées !
</div>

<section id="decouvrir" class="grille">

    <a class="carte carte-lien reveal" href="index.php?page=planning">
        <span class="icone" aria-hidden="true">
            <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
        </span>
        <div>
            <h3>Planning complet</h3>
            <p>Tous les cours et toutes les pratiques au même endroit.</p>
        </div>
        <span class="lien-fleche">Voir le planning</span>
    </a>

    <a class="carte carte-lien reveal" href="index.php?page=cours">
        <span class="icone" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.2c3 .2 5.5 2.2 5.5 5.3"/></svg>
        </span>
        <div>
            <h3>Cours en groupe</h3>
            <p>Apprends avec d'autres élèves, encadré par un organisateur.</p>
        </div>
        <span class="lien-fleche">Voir les cours</span>
    </a>

    <a class="carte carte-lien reveal" href="index.php?page=pratique">
        <span class="icone" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M9 18V5l11-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="17" cy="16" r="3"/></svg>
        </span>
        <div>
            <h3>Pratique libre</h3>
            <p>Viens pratiquer l'instrument de ton choix pendant une séance.</p>
        </div>
        <span class="lien-fleche">Voir les pratiques</span>
    </a>

</section>
