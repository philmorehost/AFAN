<?php
/**
 * Stage 2: Database & SMS Configuration
 */

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $sms_api_key = $_POST['sms_api_key'] ?? '';
    $sms_sender = $_POST['sms_sender'] ?? 'AFAN-FISP';

    // Test DB Connection
    try {
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
        $test_db = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        $_SESSION['install_data']['db'] = [
            'host' => $db_host,
            'name' => $db_name,
            'user' => $db_user,
            'pass' => $db_pass
        ];
        $_SESSION['install_data']['sms'] = [
            'api_key' => $sms_api_key,
            'sender' => $sms_sender
        ];

        header("Location: index.php?stage=3");
        exit;
    } catch (PDOException $e) {
        $error = "Database Connection Failed: " . $e->getMessage();
    }
}

?>

<h3>Database & SMS Setup</h3>
<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label>MySQL Host</label>
        <input type="text" name="db_host" value="localhost" required>
    </div>
    <div class="form-group">
        <label>Database Name</label>
        <input type="text" name="db_name" required>
    </div>
    <div class="form-group">
        <label>MySQL User</label>
        <input type="text" name="db_user" required>
    </div>
    <div class="form-group">
        <label>MySQL Password</label>
        <input type="password" name="db_pass">
    </div>
    
    <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #e5e7eb;">
    
    <h3>PhilmoreSMS Configuration (v2)</h3>
    <div class="form-group">
        <label>API Key</label>
        <input type="text" name="sms_api_key" placeholder="Enter PhilmoreSMS API Key" required>
    </div>
    <div class="form-group">
        <label>Sender ID</label>
        <input type="text" name="sms_sender" value="AFAN-FISP" maxlength="11" required>
    </div>

    <button type="submit" class="btn">Test & Continue</button>
</form>
