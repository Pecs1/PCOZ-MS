<?php
// **WARNING: This file contains sensitive information. Ensure it is not accessible from the web.**
// **ONLY USE THIS FOR LOCAL DEVELOPMENT OR SECURE ENVIRONMENTS.**

// Database settings
define('DB_HOST', getenv('DB_HOST') ?: 'localhost'); // Use environment variable or default to 'localhost'
define('DB_NAME', getenv('DB_NAME') ?: 'pcoz-ms'); // Use environment variable or default to 'pcoz-ms'
define('DB_USER', getenv('DB_USER') ?: 'root'); // Use environment variable or default to 'root'
define('DB_PASS', getenv('DB_PASS') ?: ''); // Use environment variable or default to an empty password
define('DB_CHARSET', 'utf8mb4'); // Database charset for proper encoding

// Individual checks for better debugging **ONLY USE FOR DEVELOPMENT!**
if (!DB_HOST) {
    die("Database host is not set. Please set the DB_HOST environment variable.");
}
if (!DB_USER) {
    die("Database user is not set. Please set the DB_USER environment variable.");
}
// Allow empty password for root user (default in XAMPP)
if (DB_PASS === null) {
    die("Database password is not set correctly. Please set the DB_PASS environment variable.");
}
if (!DB_NAME) {
    die("Database name is not set. Please set the DB_NAME environment variable.");
}

// In production it might look like this:

// Check if any required configuration is missing
// if (!DB_HOST || !DB_USER || !DB_PASS || !DB_NAME) {
//    die("Database configuration is incomplete or wrong. Check for typos. Please correct these db settings: DB_HOST, DB_USER, DB_PASS, DB_NAME.");
// }



// These configuration will be modified or not used in production

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']), // Use secure cookies if HTTPS is enabled
        'samesite' => 'Strict',
    ]);
    session_start();
}

// Other configuration settings
$config = [
    'session' => [
        'timeout' => 1800, // 30 minutes
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
    ],
];

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

