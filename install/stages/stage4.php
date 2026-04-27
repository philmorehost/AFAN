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
    
    $config_content .= "define('SMS_API_KEY', '" . addslashes($data['sms']['api_key']) . "');\n";
    $config_content .= "define('SMS_SENDER_ID', '" . addslashes($data['sms']['sender']) . "');\n\n";
    
    $config_content .= "define('SMTP_HOST', '" . addslashes($data['smtp']['host']) . "');\n";
    $config_content .= "define('SMTP_PORT', " . (int)$data['smtp']['port'] . ");\n";
    $config_content .= "define('SMTP_AUTH', true);\n";
    $config_content .= "define('SMTP_USER', '" . addslashes($data['smtp']['user']) . "');\n";
    $config_content .= "define('SMTP_PASS', '" . addslashes($data['smtp']['pass']) . "');\n";
    $config_content .= "define('SMTP_ENCR', '" . addslashes($data['smtp']['encr']) . "');\n";
    $config_content .= "define('SMTP_FROM', '" . addslashes($data['smtp']['from']) . "');\n";

    if (file_put_contents('../inc/config.php', $config_content)) {
        // 2. Initialize Database & Create Admin Account
        try {
            $dsn = "mysql:host=" . $data['db']['host'] . ";dbname=" . $data['db']['name'] . ";charset=utf8mb4";
            $pdo = new PDO($dsn, $data['db']['user'], $data['db']['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Import Schema
            $sql = file_get_contents('schema.sql');
            $pdo->exec($sql);

            // Create Super Admin Role
            $role_stmt = $pdo->prepare("INSERT INTO roles (name, permissions) VALUES (?, ?)");
            $role_stmt->execute(['Super Admin', json_encode(['all'])]);
            $role_id = $pdo->lastInsertId();

            // Create Admin User
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$admin_user, $admin_email, password_hash($admin_pass, PASSWORD_DEFAULT), $role_id]);

            $_SESSION['install_complete'] = true;
        } catch (PDOException $e) {
            $error = "DB Error during initialization: " . $e->getMessage();
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
