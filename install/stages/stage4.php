<?php
/**
 * Stage 4: Admin Account & Finalization
 */

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_user = $_POST['admin_user'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_pass = $_POST['admin_pass'] ?? '';

    // 1. Generate config.php
    $data = $_SESSION['install_data'];
    $config_content = "<?php\n";
    $config_content .= "/** AFAN Configuration - Auto-generated */\n\n";
    $config_content .= "define('DB_HOST', '" . addslashes($data['db']['host']) . "');\n";
    $config_content .= "define('DB_NAME', '" . addslashes($data['db']['name']) . "');\n";
    $config_content .= "define('DB_USER', '" . addslashes($data['db']['user']) . "');\n";
    $config_content .= "define('DB_PASS', '" . addslashes($data['db']['pass']) . "');\n\n";
    
    $config_content .= "define('SMS_TOKEN', '" . addslashes($data['sms']['token']) . "');\n";
    $config_content .= "define('SMS_SENDER_ID', '" . addslashes($data['sms']['sender']) . "');\n\n";
    
    $config_content .= "define('SMTP_HOST', '" . addslashes($data['smtp']['host']) . "');\n";
    $config_content .= "define('SMTP_PORT', " . (int)$data['smtp']['port'] . ");\n";
    $config_content .= "define('SMTP_AUTH', true);\n";
    $config_content .= "define('SMTP_USER', '" . addslashes($data['smtp']['user']) . "');\n";
    $config_content .= "define('SMTP_PASS', '" . addslashes($data['smtp']['pass']) . "');\n";
    $config_content .= "define('SMTP_ENCR', '" . addslashes($data['smtp']['encr']) . "');\n";
    $config_content .= "define('SMTP_FROM', '" . addslashes($data['smtp']['from']) . "');\n";

    if (file_put_contents('../inc/config.php', $config_content)) {
        // 2. Create Admin Account in DB
        try {
            $dsn = "mysql:host=" . $data['db']['host'] . ";dbname=" . $data['db']['name'];
            $pdo = new PDO($dsn, $data['db']['user'], $data['db']['pass']);
            
            // Create Users Table if not exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE,
                email VARCHAR(100) UNIQUE,
                password VARCHAR(255),
                role ENUM('admin', 'agent') DEFAULT 'admin',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
            $stmt->execute([$admin_user, $admin_email, password_hash($admin_pass, PASSWORD_DEFAULT)]);

            $_SESSION['install_complete'] = true;
        } catch (PDOException $e) {
            $error = "DB Error during admin creation: " . $e->getMessage();
        }
    } else {
        $error = "Failed to write inc/config.php. Check permissions.";
    }
}

?>

<h3>Admin Setup</h3>
<?php if (isset($_SESSION['install_complete'])): ?>
    <div class="alert alert-success">
        <strong>Installation Successful!</strong><br>
        SYSTEM ALERT: Please delete the <code>/install</code> folder and update your file permissions to 644 for <code>inc/config.php</code>.
    </div>
    <a href="../index.php" class="btn">Launch Dashboard</a>
<?php else: ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Master Admin Username</label>
            <input type="text" name="admin_user" value="admin" required>
        </div>
        <div class="form-group">
            <label>Admin Email</label>
            <input type="email" name="admin_email" required>
        </div>
        <div class="form-group">
            <label>Admin Password</label>
            <input type="password" name="admin_pass" required>
        </div>
        <button type="submit" class="btn">Finalize Installation</button>
    </form>
<?php endif; ?>
