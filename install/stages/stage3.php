<?php
/**
 * Stage 3: SMTP Configuration
 */

// Load Composer Autoloader for PHPMailer
if (file_exists(dirname(__DIR__, 2) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
}

require_once '../inc/services/CommService.php';

$msg = '';
$msg_type = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smtp_host = $_POST['smtp_host'] ?? '';
    $smtp_port = $_POST['smtp_port'] ?? '';
    $smtp_user = $_POST['smtp_user'] ?? '';
    $smtp_pass = $_POST['smtp_pass'] ?? '';
    $smtp_encr = $_POST['smtp_encr'] ?? 'tls';
    $smtp_from = $_POST['smtp_from'] ?? '';

    $_SESSION['install_data']['smtp'] = [
        'host' => $smtp_host,
        'port' => $smtp_port,
        'user' => $smtp_user,
        'pass' => $smtp_pass,
        'encr' => $smtp_encr,
        'from' => $smtp_from
    ];

    if (isset($_POST['test_email'])) {
        // Mocking constants for CommService test
        if (!defined('SMTP_HOST')) define('SMTP_HOST', $smtp_host);
        if (!defined('SMTP_PORT')) define('SMTP_PORT', $smtp_port);
        if (!defined('SMTP_AUTH')) define('SMTP_AUTH', true);
        if (!defined('SMTP_USER')) define('SMTP_USER', $smtp_user);
        if (!defined('SMTP_PASS')) define('SMTP_PASS', $smtp_pass);
        if (!defined('SMTP_ENCR')) define('SMTP_ENCR', $smtp_encr);
        if (!defined('SMTP_FROM')) define('SMTP_FROM', $smtp_from);
        if (!defined('APP_NAME')) define('APP_NAME', 'AFAN Installation Test');

        $comm = new CommService();
        if ($comm->sendEmail($smtp_from, "AFAN Installation Test", "Your SMTP settings are working correctly!")) {
            $msg = "Test email sent successfully to $smtp_from!";
            $msg_type = 'success';
        } else {
            $msg = "SMTP Test failed. Check your credentials and server logs.";
            $msg_type = 'error';
        }
    } else {
        header("Location: index.php?stage=4");
        exit;
    }
}

?>

<h3>SMTP Configuration</h3>
<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $msg; ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label>SMTP Host</label>
        <input type="text" name="smtp_host" placeholder="smtp.gmail.com" value="<?php echo $_SESSION['install_data']['smtp']['host'] ?? ''; ?>" required>
    </div>
    <div class="form-group">
        <label>SMTP Port</label>
        <input type="number" name="smtp_port" value="<?php echo $_SESSION['install_data']['smtp']['port'] ?? '587'; ?>" required>
    </div>
    <div class="form-group">
        <label>SMTP Encryption</label>
        <select name="smtp_encr" style="width:100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
            <option value="tls" <?php echo ($_SESSION['install_data']['smtp']['encr'] ?? 'tls') == 'tls' ? 'selected' : ''; ?>>TLS (Standard)</option>
            <option value="ssl" <?php echo ($_SESSION['install_data']['smtp']['encr'] ?? '') == 'ssl' ? 'selected' : ''; ?>>SSL (Implicit)</option>
            <option value="none" <?php echo ($_SESSION['install_data']['smtp']['encr'] ?? '') == 'none' ? 'selected' : ''; ?>>None</option>
        </select>
    </div>
    <div class="form-group">
        <label>SMTP Username</label>
        <input type="text" name="smtp_user" value="<?php echo $_SESSION['install_data']['smtp']['user'] ?? ''; ?>" required>
    </div>
    <div class="form-group">
        <label>SMTP Password</label>
        <input type="password" name="smtp_pass" value="<?php echo $_SESSION['install_data']['smtp']['pass'] ?? ''; ?>" required>
    </div>
    <div class="form-group">
        <label>System From Email</label>
        <input type="email" name="smtp_from" placeholder="noreply@afan-fisp.org" value="<?php echo $_SESSION['install_data']['smtp']['from'] ?? ''; ?>" required>
    </div>

    <div style="display: flex; gap: 1rem;">
        <button type="submit" name="test_email" class="btn" style="background: #6b7280;">Test Connection</button>
        <button type="submit" class="btn">Continue</button>
    </div>
</form>
