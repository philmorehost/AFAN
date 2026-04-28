<?php
/**
 * AFAN Database Connection
 */

if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $db = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // During installation, this might fail if config isn't ready
        if (defined('INSTALLING')) {
            return;
        }
        // If config exists but connection fails, provide a helpful message or redirect
        error_log("Database Connection Failed: " . $e->getMessage());
        if (!isset($is_api_request)) {
             // header("Location: error.php?type=db");
        }
    }
} else {
    // Configuration not loaded
    if (!defined('INSTALLING') && basename($_SERVER['PHP_SELF']) != 'index.php') {
        // Potential redirect to installer could be handled here or in init.php
    }
}
