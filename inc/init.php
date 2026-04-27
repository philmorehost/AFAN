<?php
/**
 * AFAN Initialization
 */

// Error reporting for development - adjust for production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Configuration
require_once __DIR__ . '/config.php';

// Load Helper Functions
require_once __DIR__ . '/functions.php';

// Load Database Connection
require_once __DIR__ . '/db.php';

// Autoload Services
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/services/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// System Constants
define('APP_NAME', 'AFAN Food Security Platform');
define('VERSION', '1.0.0');
