<?php
/**
 * AFAN System Settings
 */
require_once 'inc/init.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$adminService = new AdminService($db);
if (!$adminService->hasPermission($_SESSION['user_id'], 'all')) { // Only Super Admin
    die("Unauthorized access: You do not have permission to manage system settings.");
}

$settingsService = new SettingsService($db);
$message = '';
$error = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    foreach ($_POST['settings'] as $key => $value) {
        $group = $_POST['groups'][$key] ?? 'general';
        $settingsService->set($key, $value, $group);
    }
    $message = "Settings updated successfully.";
    log_audit($_SESSION['user_id'], 'update_settings', "Updated system settings");
}

$smtp_settings = $settingsService->getByGroup('smtp');
$sms_settings = $settingsService->getByGroup('sms');
$nin_settings = $settingsService->getByGroup('nin');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - AFAN Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --secondary: #10b981;
            --dark: #111827;
            --light: #f9fafb;
            --card-bg: #ffffff;
            --text-main: #374151;
        }
        body { font-family: 'Outfit', sans-serif; background-color: var(--light); color: var(--text-main); margin: 0; display: flex; flex-direction: column; }
        @media (min-width: 768px) { body { flex-direction: row; } }

        .sidebar { width: 100%; background: var(--dark); color: white; padding: 1rem; box-sizing: border-box; }
        @media (min-width: 768px) { .sidebar { width: 260px; height: 100vh; position: fixed; padding: 2rem 1rem; } }
        .sidebar h2 { font-size: 1.25rem; margin-bottom: 2rem; color: var(--secondary); text-align: center; }
        .nav-link { display: block; padding: 0.75rem 1rem; color: #9ca3af; text-decoration: none; border-radius: 0.5rem; margin-bottom: 0.5rem; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { background: #1f2937; color: white; }

        .main-content { padding: 1.5rem; width: 100%; box-sizing: border-box; }
        @media (min-width: 768px) { .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 2rem; } }

        .card { background: var(--card-bg); border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 2rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.4rem; font-size: 0.875rem; color: #4b5563; font-weight: 600; }
        input { width: 100%; padding: 0.6rem; border: 1px solid #d1d5db; border-radius: 0.4rem; box-sizing: border-box; }

        .btn { background: var(--primary); color: white; padding: 0.6rem 1.2rem; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .btn:hover { background: var(--secondary); }

        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .alert-success { background: #d1fae5; color: #065f46; }

        .settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        h4 { border-bottom: 1px solid #f3f4f6; padding-bottom: 0.5rem; margin-top: 0; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>AFAN FISP</h2>
        <nav>
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="beneficiaries.php" class="nav-link">Beneficiaries</a>
            <a href="programs.php" class="nav-link">Support Programs</a>
            <a href="tokens.php" class="nav-link">Token Redemption</a>
            <a href="admins.php" class="nav-link">Administrators</a>
            <a href="roles.php" class="nav-link">Roles & Permissions</a>
            <a href="settings.php" class="nav-link active">System Settings</a>
            <a href="cms_landing.php" class="nav-link">Landing Page CMS</a>
            <a href="pages.php" class="nav-link">Custom Pages</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header style="margin-bottom: 2rem;">
            <h3 style="margin: 0;">System Settings</h3>
            <p style="margin: 0; color: #6b7280;">Manage API keys, SMTP, and site configurations.</p>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php csrf_field(); ?>
            <div class="settings-grid">
                <!-- SMTP Settings -->
                <div class="card">
                    <h4>SMTP Configuration</h4>
                    <div class="form-group">
                        <label>SMTP Host</label>
                        <input type="text" name="settings[smtp_host]" value="<?php echo htmlspecialchars($smtp_settings['smtp_host'] ?? SMTP_HOST); ?>">
                        <input type="hidden" name="groups[smtp_host]" value="smtp">
                    </div>
                    <div class="form-group">
                        <label>SMTP Port</label>
                        <input type="number" name="settings[smtp_port]" value="<?php echo htmlspecialchars($smtp_settings['smtp_port'] ?? SMTP_PORT); ?>">
                        <input type="hidden" name="groups[smtp_port]" value="smtp">
                    </div>
                    <div class="form-group">
                        <label>SMTP Username</label>
                        <input type="text" name="settings[smtp_user]" value="<?php echo htmlspecialchars($smtp_settings['smtp_user'] ?? SMTP_USER); ?>">
                        <input type="hidden" name="groups[smtp_user]" value="smtp">
                    </div>
                    <div class="form-group">
                        <label>SMTP Password</label>
                        <input type="password" name="settings[smtp_pass]" value="<?php echo htmlspecialchars($smtp_settings['smtp_pass'] ?? SMTP_PASS); ?>">
                        <input type="hidden" name="groups[smtp_pass]" value="smtp">
                    </div>
                    <div class="form-group">
                        <label>SMTP From Email</label>
                        <input type="email" name="settings[smtp_from]" value="<?php echo htmlspecialchars($smtp_settings['smtp_from'] ?? SMTP_FROM); ?>">
                        <input type="hidden" name="groups[smtp_from]" value="smtp">
                    </div>
                </div>

                <!-- SMS & NIN Settings -->
                <div class="card">
                    <h4>API Configurations</h4>
                    <div class="form-group">
                        <label>PhilmoreSMS API Key</label>
                        <input type="password" name="settings[sms_api_key]" value="<?php echo htmlspecialchars($sms_settings['sms_api_key'] ?? SMS_API_KEY); ?>">
                        <input type="hidden" name="groups[sms_api_key]" value="sms">
                    </div>
                    <div class="form-group">
                        <label>PhilmoreSMS Sender ID</label>
                        <input type="text" name="settings[sms_sender_id]" value="<?php echo htmlspecialchars($sms_settings['sms_sender_id'] ?? SMS_SENDER_ID); ?>">
                        <input type="hidden" name="groups[sms_sender_id]" value="sms">
                    </div>
                    <hr style="margin: 1.5rem 0; border: 0; border-top: 1px solid #f3f4f6;">
                    <div class="form-group">
                        <label>Datagifting NIN API Key</label>
                        <input type="password" name="settings[nin_api_key]" value="<?php echo htmlspecialchars($nin_settings['nin_api_key'] ?? NIN_API_KEY); ?>">
                        <input type="hidden" name="groups[nin_api_key]" value="nin">
                    </div>
                </div>
            </div>

            <div class="card" style="text-align: right;">
                <button type="submit" name="update_settings" class="btn">Save All Settings</button>
            </div>
        </form>
    </div>
</body>
</html>
