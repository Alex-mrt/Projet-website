-- =====================================================================
--  Bureau des Arts — structure de la base de données MySQL
--  À exécuter UNE fois dans ta base (phpMyAdmin → onglet SQL → coller → Exécuter)
-- =====================================================================

SET NAMES utf8mb4;

-- Comptes élèves
CREATE TABLE IF NOT EXISTS utilisateurs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(190) NOT NULL UNIQUE,
    prenom          VARCHAR(80)  NOT NULL,
    nom             VARCHAR(80)  NOT NULL,
    mot_de_passe    VARCHAR(255) NOT NULL,           -- hash bcrypt/argon2, jamais en clair
    verifie_le      DATETIME     NULL,               -- NULL tant que l'e-mail n'est pas confirmé
    cree_le         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Liens de confirmation d'adresse e-mail
CREATE TABLE IF NOT EXISTS verifications_email (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    token_hash      CHAR(64)     NOT NULL UNIQUE,    -- sha256 du token envoyé par mail
    expire_le       DATETIME     NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tokens de connexion longue durée (30 jours glissants)
CREATE TABLE IF NOT EXISTS tokens_connexion (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    selecteur       CHAR(24)     NOT NULL UNIQUE,    -- partie publique du cookie
    validateur_hash CHAR(64)     NOT NULL,           -- sha256 de la partie secrète
    expire_le       DATETIME     NOT NULL,
    cree_le         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX (expire_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Essais de connexion (anti-bruteforce)
CREATE TABLE IF NOT EXISTS essais_connexion (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(190) NOT NULL,
    ip              VARCHAR(45)  NOT NULL,
    essaye_le       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (email, essaye_le),
    INDEX (ip, essaye_le)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Réservations des créneaux
CREATE TABLE IF NOT EXISTS reservations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    creneau_id      VARCHAR(40)  NOT NULL,           -- ex : 2026-10-06_18:00_cours
    classe          VARCHAR(40)  NOT NULL,
    instrument      VARCHAR(40)  NULL,
    cree_le         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY une_resa_par_creneau (utilisateur_id, creneau_id),
    INDEX (creneau_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
