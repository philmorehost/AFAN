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
    return bin2hex(random_bytes($length));
}

/**
 * CSRF Protection
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}

/**
 * Log message to audit trail
 */
function log_audit($user_id, $action, $details = '') {
    global $db;
    if (!$db) return;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $table = 'audit_trail_' . date('Y_m'); // Month-based partitioning
    
    try {
        // Check if table exists, if not create it (simple partitioning logic)
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
    } catch (PDOException $e) {
        error_log("Audit Log Error: " . $e->getMessage());
    }
}
