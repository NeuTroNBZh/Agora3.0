<?php
// Configuration générale
define('SITE_NAME', 'Agora');
define('SITE_URL', 'http://localhost/agora');
define('SITE_DESCRIPTION', 'La plateforme communautaire pour les créateurs de contenu');
define('ADMIN_EMAIL', 'contact@agora.com');

// Configuration des chemins
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('PORTFOLIO_PATH', UPLOAD_PATH . '/portfolios');
define('PROFILE_IMAGES_PATH', UPLOAD_PATH . '/profiles');
define('ARTICLE_IMAGES_PATH', UPLOAD_PATH . '/articles');
define('EVENT_IMAGES_PATH', UPLOAD_PATH . '/events');

// Configuration des fichiers
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf']);

// Configuration des emails
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'contact@agora.com');
define('SMTP_FROM_NAME', 'Agora');

// Configuration de la sécurité
define('SESSION_LIFETIME', 3600); // 1 heure
define('PASSWORD_MIN_LENGTH', 8);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Configuration des médias sociaux
define('DISCORD_INVITE_URL', 'https://discord.gg/your-invite-code');
define('TWITTER_URL', 'https://twitter.com/agora');
define('INSTAGRAM_URL', 'https://instagram.com/agora');
define('YOUTUBE_URL', 'https://youtube.com/agora');

// Configuration des fonctionnalités
define('ENABLE_USER_REGISTRATION', true);
define('ENABLE_CREATOR_APPLICATIONS', true);
define('ENABLE_COMMENTS', true);
define('ENABLE_RATINGS', true);
define('ENABLE_SHARING', true);

// Configuration du cache
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 heure
define('CACHE_PATH', ROOT_PATH . '/cache');

// Configuration des logs
define('LOG_ENABLED', true);
define('LOG_PATH', ROOT_PATH . '/logs');
define('LOG_LEVEL', 'error'); // debug, info, warning, error

// Configuration des paginations
define('ARTICLES_PER_PAGE', 10);
define('VIDEOS_PER_PAGE', 12);
define('EVENTS_PER_PAGE', 8);
define('CREATORS_PER_PAGE', 12);

// Configuration des timezones
date_default_timezone_set('Europe/Paris');

// Configuration des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . '/php-error.log');

// Création des répertoires nécessaires
$directories = [
    UPLOAD_PATH,
    PORTFOLIO_PATH,
    PROFILE_IMAGES_PATH,
    ARTICLE_IMAGES_PATH,
    EVENT_IMAGES_PATH,
    CACHE_PATH,
    LOG_PATH
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Fonction pour charger les variables d'environnement
function loadEnv() {
    $envFile = ROOT_PATH . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Chargement des variables d'environnement
loadEnv(); 