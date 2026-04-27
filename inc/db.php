<?php
/**
 * AFAN Database Connection
 */

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
    // We handle this gracefully or the installer skips loading this
    if (defined('INSTALLING')) {
        return;
    }
    die("Database Connection Failed: " . $e->getMessage());
}
