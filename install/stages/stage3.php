<?php
/**
 * Stage 3: SMTP Configuration
 */

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
        // Test SMTP
        // Note: We need PHPMailer for this. If not yet installed, we can skip or use a simple mail() test.
        // For now, we'll just save and let them proceed, or try mail() as a basic test.
        if (mail($smtp_from, "AFAN Installation Test", "If you received this, your server can send basic mail. Note: SMTP settings will be applied to PHPMailer later.")) {
            $msg = "Basic mail() test successful! SMTP settings saved.";
            $msg_type = 'success';
        } else {
            $msg = "Mail test failed, but settings are saved. Verify your SMTP credentials.";
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
        <input type="text" name="smtp_host" placeholder="smtp.gmail.com" required>
    </div>
    <div class="form-group">
        <label>SMTP Port</label>
        <input type="number" name="smtp_port" value="587" required>
    </div>
    <div class="form-group">
        <label>SMTP Encryption</label>
        <select name="smtp_encr" style="width:100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
            <option value="tls">TLS (Standard)</option>
            <option value="ssl">SSL (Implicit)</option>
            <option value="none">None</option>
        </select>
    </div>
    <div class="form-group">
        <label>SMTP Username</label>
        <input type="text" name="smtp_user" required>
    </div>
    <div class="form-group">
        <label>SMTP Password</label>
        <input type="password" name="smtp_pass" required>
    </div>
    <div class="form-group">
        <label>System From Email</label>
        <input type="email" name="smtp_from" placeholder="noreply@afan-fisp.org" required>
    </div>

    <div style="display: flex; gap: 1rem;">
        <button type="submit" name="test_email" class="btn" style="background: #6b7280;">Test Connection</button>
        <button type="submit" class="btn">Continue</button>
    </div>
</form>
