<?php
/**
 * AFAN Helper Functions
 */

/**
 * Sanitize input data
 */
function sanitize($data, $type = 'string') {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($value, $type);
        }
        return $data;
    }

    $data = trim($data);
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($data, FILTER_SANITIZE_URL);
        case 'int':
            return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        default:
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Generate a cryptographically secure token
 */
function generateToken($length = 4) {
    try {
        return bin2hex(random_bytes($length));
    } catch (Exception $e) {
        // Fallback for systems without random_bytes
        return substr(md5(uniqid(mt_rand(), true)), 0, $length * 2);
    }
}

/**
 * Log message to audit trail
 */
function log_audit($user_id, $action, $details = '') {
    global $db;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $table = 'audit_trail_' . date('Y_m'); // Month-based partitioning
    
    // Check if table exists, if not create it (simple partitioning logic)
    // In a real high-volume system, this check might be cached or run as a cron
    $db->query("CREATE TABLE IF NOT EXISTS `$table` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT,
        `action` VARCHAR(255),
        `details` TEXT,
        `ip_address` VARCHAR(45),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $db->prepare("INSERT INTO `$table` (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $details, $ip]);
}
