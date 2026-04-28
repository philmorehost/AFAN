<?php
/**
 * AFAN Custom Pages Management
 */
require_once 'inc/init.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$adminService = new AdminService($db);
if (!$adminService->hasPermission($_SESSION['user_id'], 'all')) {
    die("Unauthorized access: You do not have permission to manage custom pages.");
}

$settingsService = new SettingsService($db);
$message = '';
$error = '';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    if (isset($_POST['create_page'])) {
        $title = sanitize($_POST['title']);
        $slug = sanitize($_POST['slug']);
        $content = $_POST['content']; // HTML allowed

        if ($settingsService->createPage($title, $slug, $content)) {
            $message = "Page created successfully.";
        } else {
            $error = "Failed to create page (slug must be unique).";
        }
    }

    if (isset($_POST['update_page'])) {
        $id = (int)$_POST['page_id'];
        $title = sanitize($_POST['title']);
        $slug = sanitize($_POST['slug']);
        $content = $_POST['content'];
        $is_published = isset($_POST['is_published']) ? 1 : 0;

        if ($settingsService->updatePage($id, $title, $slug, $content, $is_published)) {
            $message = "Page updated successfully.";
        } else {
            $error = "Failed to update page.";
        }
    }
}

if (isset($_GET['delete'])) {
    $settingsService->deletePage((int)$_GET['delete']);
    $message = "Page deleted.";
}

$pages = $settingsService->getPages();
$edit_page = null;
if (isset($_GET['edit'])) {
    foreach ($pages as $p) {
        if ($p['id'] == $_GET['edit']) {
            $edit_page = $p;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pages - AFAN Platform</title>
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

        .btn { padding: 0.6rem 1.2rem; border-radius: 0.5rem; border: none; cursor: pointer; font-weight: 600; transition: all 0.2s; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--secondary); }

        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }

        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 1rem; border-bottom: 1px solid #f3f4f6; }
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
            <a href="cms_landing.php" class="nav-link">Landing Page CMS</a>
            <a href="pages.php" class="nav-link active">Custom Pages</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header style="margin-bottom: 2rem;">
            <h3 style="margin: 0;">Custom Site Pages</h3>
            <p style="margin: 0; color: #6b7280;">Create and manage additional pages for the website.</p>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h4><?php echo $edit_page ? 'Edit Page' : 'Create New Page'; ?></h4>
            <form method="POST">
                <?php csrf_field(); ?>
                <?php if ($edit_page): ?>
                    <input type="hidden" name="page_id" value="<?php echo $edit_page['id']; ?>">
                <?php endif; ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Page Title</label>
                        <input type="text" name="title" value="<?php echo $edit_page ? htmlspecialchars($edit_page['title']) : ''; ?>" required placeholder="e.g. About Us">
                    </div>
                    <div class="form-group">
                        <label>URL Slug</label>
                        <input type="text" name="slug" value="<?php echo $edit_page ? htmlspecialchars($edit_page['slug']) : ''; ?>" required placeholder="e.g. about-us">
                    </div>
                </div>
                <div class="form-group">
                    <label>Content (HTML Supported)</label>
                    <textarea name="content" rows="10" required><?php echo $edit_page ? htmlspecialchars($edit_page['content']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 400;">
                        <input type="checkbox" name="is_published" style="width: auto;" <?php echo (!$edit_page || $edit_page['is_published']) ? 'checked' : ''; ?>>
                        Published
                    </label>
                </div>
                <button type="submit" name="<?php echo $edit_page ? 'update_page' : 'create_page'; ?>" class="btn btn-primary">
                    <?php echo $edit_page ? 'Update Page' : 'Create Page'; ?>
                </button>
                <?php if ($edit_page): ?>
                    <a href="pages.php" class="btn" style="background: #e5e7eb;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h4>Existing Pages</h4>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $p): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($p['title']); ?></strong></td>
                                <td><code>/page.php?slug=<?php echo $p['slug']; ?></code></td>
                                <td><?php echo $p['is_published'] ? '<span style="color: green;">Live</span>' : '<span style="color: gray;">Draft</span>'; ?></td>
                                <td>
                                    <a href="?edit=<?php echo $p['id']; ?>" style="color: var(--primary);">Edit</a> |
                                    <a href="?delete=<?php echo $p['id']; ?>" style="color: #991b1b;" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pages)): ?>
                            <tr><td colspan="4" style="text-align: center; color: #9ca3af;">No pages created yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
