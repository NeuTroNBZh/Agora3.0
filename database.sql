-- Création de la base de données
CREATE DATABASE IF NOT EXISTS agora_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agora_db;

-- Table des messages de contact
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des candidatures de créateurs
CREATE TABLE IF NOT EXISTS creator_applications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    pseudo VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    platforms VARCHAR(255) NOT NULL,
    links TEXT,
    description TEXT,
    motivation TEXT,
    followers VARCHAR(50),
    contact VARCHAR(255),
    examples TEXT,
    portfolio_files TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des articles
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    author VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des vidéos
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    video_url VARCHAR(255) NOT NULL,
    thumbnail_url VARCHAR(255),
    creator_id INT,
    created_at DATETIME NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    FOREIGN KEY (creator_id) REFERENCES creator_applications(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des événements
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    location VARCHAR(255),
    image_url VARCHAR(255),
    created_at DATETIME NOT NULL,
    status ENUM('draft', 'published', 'cancelled') DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des créateurs
CREATE TABLE IF NOT EXISTS creators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT,
    name VARCHAR(100) NOT NULL,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    platforms VARCHAR(255) NOT NULL,
    social_links TEXT,
    bio TEXT,
    profile_image VARCHAR(255),
    created_at DATETIME NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (application_id) REFERENCES creator_applications(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des utilisateurs administrateurs
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'moderator') NOT NULL,
    created_at DATETIME NOT NULL,
    last_login DATETIME,
    status ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les recherches
CREATE INDEX idx_articles_status ON articles(status);
CREATE INDEX idx_videos_status ON videos(status);
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_creators_status ON creators(status);
CREATE INDEX idx_contact_messages_status ON contact_messages(status);
CREATE INDEX idx_creator_applications_status ON creator_applications(status);

-- Insertion d'un administrateur par défaut (mot de passe: admin123)
INSERT INTO admins (username, email, password, full_name, role, created_at)
VALUES ('admin', 'admin@agora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'admin', NOW()); 