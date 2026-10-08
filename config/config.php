<?php
// =====================================================================
//  CONFIGURATION DU SITE — C'EST ICI QUE TU METS TES IDENTIFIANTS
// =====================================================================
//
//  Chaque valeur peut venir d'une variable d'environnement (recommandé en
//  production) OU être écrite directement à la place du texte '...'.
//
//  ATTENTION : si tu écris tes vrais mots de passe dans ce fichier,
//  ne le pousse PAS sur un dépôt GitHub public.
// =====================================================================


// ---------- BASE DE DONNÉES MYSQL ----------
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');          // ← adresse du serveur MySQL (ex : localhost ou mysql.monhebergeur.com)
define('DB_PORT', getenv('DB_PORT') ?: '3306');               // ← port MySQL (3306 par défaut)
define('DB_NAME', getenv('DB_NAME') ?: 'NOM_DE_TA_BASE');     // ← nom de ta base de données
define('DB_USER', getenv('DB_USER') ?: 'TON_UTILISATEUR');    // ← ton identifiant MySQL
define('DB_PASS', getenv('DB_PASS') ?: 'TON_MOT_DE_PASSE');   // ← ton mot de passe MySQL


// ---------- ADRESSE DU SITE ----------
// URL complète du site, SANS slash à la fin. Sert à construire le lien de
// confirmation envoyé par e-mail.
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:3000');   // ← ex : https://bda.jsnee.com


// ---------- RESTRICTION À L'ÉCOLE ----------
// Seules les adresses se terminant par ce domaine peuvent créer un compte.
define('DOMAINE_AUTORISE', 'jsnee.com');


// ---------- E-MAILS DE CONFIRMATION ----------
// MAIL_METHODE :
//   'smtp' → recommandé (Gmail, Outlook, Brevo, OVH, etc.)
//   'mail' → fonction mail() de PHP (marche seulement si ton hébergeur l'autorise)
define('MAIL_METHODE',  getenv('MAIL_METHODE')  ?: 'smtp');
define('MAIL_EXPEDITEUR', getenv('MAIL_EXPEDITEUR') ?: 'no-reply@jsnee.com'); // ← adresse qui envoie les e-mails
define('MAIL_NOM',      'Bureau des Arts');

define('SMTP_HOTE',     getenv('SMTP_HOTE')     ?: 'smtp.exemple.com'); // ← ex : smtp.gmail.com / smtp-relay.brevo.com / ssl0.ovh.net
define('SMTP_PORT',     getenv('SMTP_PORT')     ?: '587');              // ← 587 (STARTTLS) ou 465 (SSL)
define('SMTP_USER',     getenv('SMTP_USER')     ?: 'TON_LOGIN_SMTP');   // ← identifiant SMTP
define('SMTP_PASS',     getenv('SMTP_PASS')     ?: 'TON_MDP_SMTP');     // ← mot de passe SMTP (pour Gmail : un "mot de passe d'application")


// ---------- SESSIONS ----------
// Durée de la connexion "se souvenir de moi". Chaque visite remet le compteur à 30 jours.
define('DUREE_TOKEN_JOURS', 30);

// Durée de validité du lien de confirmation envoyé par e-mail.
define('DUREE_LIEN_CONFIRMATION_HEURES', 24);

// Anti-bruteforce : nombre d'essais de connexion ratés autorisés en 15 minutes.
define('MAX_ESSAIS_CONNEXION', 5);


// ---------- MODE DÉVELOPPEMENT ----------
// true  = affiche les erreurs PHP (pratique pendant que tu construis le site)
// false = à mettre en production pour ne rien révéler aux visiteurs
define('MODE_DEV', true);
