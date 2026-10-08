document.documentElement.classList.add('js');

document.addEventListener('DOMContentLoaded', () => {
  const reduitMouvement = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ===== En-tête : ombre au défilement =====
  const header = document.querySelector('.site-header');
  const majHeader = () => header.classList.toggle('scrolle', window.scrollY > 8);
  majHeader();
  window.addEventListener('scroll', majHeader, { passive: true });

  // ===== Menu mobile =====
  const burger = document.querySelector('.burger');
  const menu = document.getElementById('menu');
  const fermerMenu = () => {
    burger.setAttribute('aria-expanded', 'false');
    burger.setAttribute('aria-label', 'Ouvrir le menu');
    menu.classList.remove('ouvert');
  };
  burger.addEventListener('click', () => {
    const ouvert = burger.getAttribute('aria-expanded') === 'true';
    if (ouvert) {
      fermerMenu();
    } else {
      burger.setAttribute('aria-expanded', 'true');
      burger.setAttribute('aria-label', 'Fermer le menu');
      menu.classList.add('ouvert');
    }
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') fermerMenu(); });
  document.addEventListener('click', (e) => {
    if (!header.contains(e.target)) fermerMenu();
  });

  // ===== Apparition des éléments au défilement (cascade) =====
  const elements = document.querySelectorAll('.reveal');
  if (reduitMouvement || !('IntersectionObserver' in window)) {
    elements.forEach((el) => el.classList.add('visible'));
  } else {
    const observer = new IntersectionObserver((entrees) => {
      let delai = 0;
      entrees.forEach((entree) => {
        if (!entree.isIntersecting) return;
        entree.target.style.transitionDelay = `${delai}ms`;
        entree.target.classList.add('visible');
        delai += 80;
        observer.unobserve(entree.target);
        entree.target.addEventListener('transitionend', () => {
          entree.target.style.transitionDelay = '';
        }, { once: true });
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    elements.forEach((el) => observer.observe(el));
  }

  // ===== Compteurs animés (statistiques de l'accueil) =====
  document.querySelectorAll('[data-compteur]').forEach((el) => {
    const cible = parseInt(el.dataset.compteur, 10);
    if (reduitMouvement || !cible) return;
    const duree = 900;
    const debut = performance.now();
    el.textContent = '0';
    const etape = (maintenant) => {
      const p = Math.min((maintenant - debut) / duree, 1);
      el.textContent = Math.round(cible * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(etape);
    };
    requestAnimationFrame(etape);
  });

  // ===== Formulaire : validation en direct + bouton de chargement =====
  const champValide = (champ) => {
    const valeur = champ.value.trim();
    if (valeur === '') return false;
    const domaine = champ.dataset.domaine;
    if (domaine && !valeur.toLowerCase().endsWith('@' + domaine)) return false;
    if (champ.minLength > 0 && valeur.length < champ.minLength) return false;
    if (champ.name === 'mot_de_passe_confirmation') {
      const mdp = champ.form.querySelector('[name="mot_de_passe"]');
      if (mdp && mdp.value !== champ.value) return false;
    }
    return true;
  };

  document.querySelectorAll('form').forEach((form) => {
    const champs = form.querySelectorAll('input[required], select[required]');
    champs.forEach((champ) => {
      const verifier = () => {
        const ok = champValide(champ);
        champ.classList.toggle('valide', ok);
        champ.classList.toggle('invalide', !ok);
      };
      champ.addEventListener('blur', verifier);
      champ.addEventListener('input', () => {
        if (champ.classList.contains('invalide')) verifier();
      });
    });

    form.addEventListener('submit', (event) => {
      const message = form.dataset.confirmer;
      if (message && !window.confirm(message)) {
        event.preventDefault();
        return;
      }
      const bouton = form.querySelector('button[type="submit"]');
      if (bouton) {
        bouton.classList.add('chargement');
        bouton.disabled = true;
        bouton.textContent = 'Envoi en cours…';
      }
    });
  });
});
