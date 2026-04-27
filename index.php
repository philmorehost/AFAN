<?php
/**
 * AFAN Food Security Platform
 * Entry Point
 */

if (!file_exists('inc/config.php')) {
    header("Location: ./install/index.php");
    exit;
}

// Load system if config exists
require_once 'inc/init.php';

// Dashboard / Landing logic here
echo "Welcome to AFAN Food Security Platform";
