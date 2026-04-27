<?php
/**
 * AFAN Dynamic Page Viewer
 */
require_once 'inc/init.php';

$slug = $_GET['slug'] ?? '';
$page = null;
$customPages = [];

if (isset($db) && $db) {
    $settingsService = new SettingsService($db);
    $page = $settingsService->getPageBySlug($slug);
    $customPages = $settingsService->getPages(true);
}

if (!$page) {
    header("HTTP/1.1 404 Not Found");
    die("Page not found.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page['title']); ?> - AFAN Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --dark: #111827;
            --light: #f9fafb;
            --text-main: #374151;
        }
        body { font-family: 'Outfit', sans-serif; background-color: var(--light); color: var(--text-main); margin: 0; line-height: 1.6; }
        header { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .container { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem; }
        nav { display: flex; justify-content: space-between; align-items: center; height: 80px; max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }
        .logo { font-size: 1.5rem; font-weight: 700; color: var(--primary); text-decoration: none; }
        .nav-links { display: flex; gap: 2rem; }
        .nav-links a { text-decoration: none; color: var(--text-main); font-weight: 500; }

        .page-header { border-bottom: 2px solid var(--primary); padding-bottom: 1rem; margin-bottom: 2rem; }
        .page-content { background: white; padding: 3rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }

        footer { padding: 3rem 0; text-align: center; color: #6b7280; border-top: 1px solid #e5e7eb; margin-top: 4rem; }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">AFAN FISP</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <?php foreach ($customPages as $p): ?>
                    <a href="page.php?slug=<?php echo $p['slug']; ?>"><?php echo htmlspecialchars($p['title']); ?></a>
                <?php endforeach; ?>
                <a href="dashboard.php" style="color: var(--primary); font-weight: 600;">Admin Login</a>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="page-content">
            <div class="page-header">
                <h1 style="margin: 0;"><?php echo htmlspecialchars($page['title']); ?></h1>
            </div>
            <div class="content">
                <?php echo $page['content']; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> AFAN - All Farmers Association of Nigeria.</p>
        </div>
    </footer>
</body>
</html>
