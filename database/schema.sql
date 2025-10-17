-- ============================================================
-- Script de création de la base de données TomTroc
-- Plateforme d'échange de livres entre particuliers
-- ============================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS tomtroc CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE tomtroc;

-- ============================================================
-- Table: utilisateurs
-- Description: Stocke les informations des utilisateurs
-- ============================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_pseudo (pseudo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Table: livres
-- Description: Stocke les informations des livres disponibles
-- ============================================================
CREATE TABLE IF NOT EXISTS livres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    auteur VARCHAR(255) NOT NULL,
    description TEXT,
    photo VARCHAR(255),
    disponible BOOLEAN DEFAULT TRUE,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_disponible (disponible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Table: echanges
-- Description: Stocke les demandes d'échange entre utilisateurs
-- ============================================================
CREATE TABLE IF NOT EXISTS echanges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livre_id INT NOT NULL,
    demandeur_id INT NOT NULL,
    proprietaire_id INT NOT NULL,
    statut ENUM('en_attente', 'accepte', 'refuse', 'termine') DEFAULT 'en_attente',
    message TEXT,
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_reponse DATETIME,
    date_finalisation DATETIME,
    FOREIGN KEY (livre_id) REFERENCES livres(id) ON DELETE CASCADE,
    FOREIGN KEY (demandeur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (proprietaire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_livre (livre_id),
    INDEX idx_demandeur (demandeur_id),
    INDEX idx_proprietaire (proprietaire_id),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Table: messages
-- Description: Stocke les messages entre utilisateurs
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    echange_id INT,
    contenu TEXT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (echange_id) REFERENCES echanges(id) ON DELETE SET NULL,
    INDEX idx_expediteur (expediteur_id),
    INDEX idx_destinataire (destinataire_id),
    INDEX idx_echange (echange_id),
    INDEX idx_lu (lu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Données de test (optionnel)
-- ============================================================

-- Insertion d'utilisateurs de test
INSERT INTO utilisateurs (pseudo, email, mot_de_passe, nom, prenom) VALUES
('jean_dupont', 'jean.dupont@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dupont', 'Jean'),
('marie_martin', 'marie.martin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Martin', 'Marie');

-- Insertion de livres de test
INSERT INTO livres (utilisateur_id, titre, auteur, description) VALUES
(1, 'Le Petit Prince', 'Antoine de Saint-Exupéry', 'Un conte poétique et philosophique sous l''apparence d''un conte pour enfants.'),
(1, '1984', 'George Orwell', 'Un roman dystopique qui dépeint une société totalitaire.'),
(2, 'L''Étranger', 'Albert Camus', 'Roman emblématique de la littérature de l''absurde.');
