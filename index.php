<?php
/**
 * AFAN Food Security Platform
 * Modern Landing Page (Dynamic Content)
 */

if (!file_exists('inc/config.php')) {
    header("Location: ./install/index.php");
    exit;
}

require_once 'inc/init.php';

$sections = [];
$customPages = [];

if (isset($db) && $db) {
    $settingsService = new SettingsService($db);
    $sections = $settingsService->getLandingSections();
    $customPages = $settingsService->getPages(true);
}

// Default content if not in DB
$hero = $sections['hero'] ?? ['title' => 'Securing the Future of Agriculture', 'content' => 'The AFAN Food Security Platform streamlines beneficiary management and resource distribution for Nigerian farmers.'];
$featHeader = $sections['features_header'] ?? ['title' => 'Advanced Distribution Management', 'content' => 'Built with modern technology to ensure transparency and efficiency in every transaction.'];
$cta = $sections['cta'] ?? ['title' => 'Ready to manage distribution?', 'content' => 'Sign in to the administrative portal to get started.'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFAN - Food Security Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --primary-dark: #065f46;
            --secondary: #10b981;
            --accent: #f59e0b;
            --dark: #111827;
            --light: #f9fafb;
            --text-main: #374151;
            --text-muted: #6b7280;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--light);
            color: var(--text-main);
            margin: 0;
            line-height: 1.6;
        }

        header {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        /* Hero Section */
        .hero {
            padding: 5rem 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 4rem;
        }

        @media (max-width: 768px) {
            .hero {
                grid-template-columns: 1fr;
                text-align: center;
                padding: 3rem 0;
            }
        }

        .hero h1 {
            font-size: 3.5rem;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
        }

        .hero-image {
            width: 100%;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        /* Features Section */
        .features {
            background: white;
            padding: 5rem 0;
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 4rem;
        }

        .section-header h2 {
            font-size: 2.25rem;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            padding: 2.5rem;
            border-radius: 1rem;
            background: var(--light);
            transition: transform 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: var(--secondary);
            color: white;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        /* CTA Section */
        .cta {
            padding: 5rem 0;
            text-align: center;
        }

        .cta-box {
            background: var(--dark);
            color: white;
            padding: 4rem 2rem;
            border-radius: 2rem;
        }

        .cta-box h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        footer {
            padding: 3rem 0;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">
                    <span style="color: var(--secondary);">AFAN</span> FISP
                </a>
                <div class="nav-links">
                    <a href="#features">Features</a>
                    <?php foreach ($customPages as $p): ?>
                        <a href="page.php?slug=<?php echo $p['slug']; ?>"><?php echo htmlspecialchars($p['title']); ?></a>
                    <?php endforeach; ?>
                    <a href="dashboard.php" class="btn btn-outline">Admin Portal</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero container">
            <div class="hero-content">
                <h1><?php echo htmlspecialchars($hero['title']); ?></h1>
                <p><?php echo nl2br(htmlspecialchars($hero['content'])); ?></p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="#features" class="btn btn-primary">Learn More</a>
                    <a href="dashboard.php" class="btn btn-outline">Admin Portal</a>
                </div>
            </div>
            <div class="hero-image-container">
                <img src="assets/img/logo.png" alt="AFAN Logo" class="hero-image" style="background: white; padding: 2rem; object-fit: contain;">
            </div>
        </section>

        <section id="features" class="features">
            <div class="container">
                <div class="section-header">
                    <h2><?php echo htmlspecialchars($featHeader['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($featHeader['content'])); ?></p>
                </div>

                <div class="feature-grid">
                    <div class="feature-card">
                        <div class="feature-icon">📱</div>
                        <h3>SMS Notifications</h3>
                        <p>Real-time alerts via PhilmoreSMS API keep farmers informed about their support status.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🛡️</div>
                        <h3>Secure Redemption</h3>
                        <p>Cryptographically secure tokens ensure that resources reach the intended beneficiaries.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Real-time Audit</h3>
                        <p>Comprehensive logging and reporting tools for transparent resource tracking.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta container">
            <div class="cta-box">
                <h2><?php echo htmlspecialchars($cta['title']); ?></h2>
                <p style="margin-bottom: 2rem; opacity: 0.8;"><?php echo nl2br(htmlspecialchars($cta['content'])); ?></p>
                <a href="dashboard.php" class="btn btn-primary" style="background: white; color: var(--dark);">Go to Dashboard</a>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> AFAN - All Farmers Association of Nigeria. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>
