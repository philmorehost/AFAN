<?php
/**
 * AFAN Landing Page CMS
 */
require_once 'inc/init.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$adminService = new AdminService($db);
if (!$adminService->hasPermission($_SESSION['user_id'], 'all')) {
    die("Unauthorized access: You do not have permission to manage landing page CMS.");
}

$settingsService = new SettingsService($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cms'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    foreach ($_POST['sections'] as $key => $data) {
        $settingsService->updateLandingSection($key, $data['title'], $data['content'], $data['image_url'] ?? null);
    }
    $message = "Landing page updated successfully.";
    log_audit($_SESSION['user_id'], 'update_landing_cms', "Updated landing page sections");
}

$sections = $settingsService->getLandingSections();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing CMS - AFAN Platform</title>
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
        input, textarea { width: 100%; padding: 0.6rem; border: 1px solid #d1d5db; border-radius: 0.4rem; box-sizing: border-box; font-family: inherit; }

        .btn { background: var(--primary); color: white; padding: 0.6rem 1.2rem; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .btn:hover { background: var(--secondary); }

        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        h4 { margin-top: 0; color: var(--primary); }
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
            <a href="settings.php" class="nav-link">System Settings</a>
            <a href="cms_landing.php" class="nav-link active">Landing Page CMS</a>
            <a href="pages.php" class="nav-link">Custom Pages</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header style="margin-bottom: 2rem;">
            <h3 style="margin: 0;">Landing Page CMS</h3>
            <p style="margin: 0; color: #6b7280;">Update sections and content on the public landing page.</p>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php csrf_field(); ?>
            <?php foreach ($sections as $key => $s): ?>
                <div class="card">
                    <h4>Section: <?php echo ucfirst($key); ?></h4>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="sections[<?php echo $key; ?>][title]" value="<?php echo htmlspecialchars($s['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Content / Description</label>
                        <textarea name="sections[<?php echo $key; ?>][content]" rows="4" required><?php echo htmlspecialchars($s['content']); ?></textarea>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="card" style="text-align: right;">
                <button type="submit" name="update_cms" class="btn">Update Landing Page</button>
            </div>
        </form>
    </div>
</body>
</html>
