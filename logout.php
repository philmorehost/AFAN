<?php
/**
 * AFAN Logout
 */
require_once 'inc/init.php';

if (isset($_SESSION['user_id'])) {
    log_audit($_SESSION['user_id'], 'logout', 'User logged out');
}

session_destroy();
header("Location: login.php");
exit;
