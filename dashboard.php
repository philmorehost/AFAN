<?php
/**
 * AFAN Admin Dashboard Shell
 */

require_once 'inc/init.php';

// Auth Check (Basic for now, installer creates first admin)
if (!isset($_SESSION['user_id'])) {
    // header("Location: login.php");
    // exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFAN Admin - Food Security Platform</title>
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
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--light);
            color: var(--text-main);
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        @media (min-width: 768px) {
            body { flex-direction: row; }
        }
        .sidebar {
            width: 100%;
            background: var(--dark);
            color: white;
            padding: 1rem;
        }
        @media (min-width: 768px) {
            .sidebar { width: 260px; height: 100vh; position: fixed; padding: 2rem 1rem; }
        }
        .sidebar h2 {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            color: var(--secondary);
            text-align: center;
        }
        .nav-link {
            display: block;
            padding: 0.75rem 1rem;
            color: #9ca3af;
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
        }
        .nav-link:hover, .nav-link.active {
            background: #1f2937;
            color: white;
        }
        .main-content {
            padding: 1.5rem;
            width: 100%;
        }
        @media (min-width: 768px) {
            .main-content { margin-left: 260px; padding: 2rem; width: calc(100% - 260px); }
        }
        .card {
            background: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            border-left: 4px solid var(--primary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stat-card h4 { margin: 0; color: #6b7280; font-size: 0.875rem; }
        .stat-card p { margin: 0.5rem 0 0; font-size: 1.5rem; font-weight: 600; }
        
        .table-container { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        th, td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        th { color: #6b7280; font-weight: 600; }
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>AFAN Platform</h2>
        <nav>
            <a href="dashboard.php" class="nav-link active">Dashboard</a>
            <a href="beneficiaries.php" class="nav-link">Beneficiaries</a>
            <a href="programs.php" class="nav-link">Support Programs</a>
            <a href="tokens.php" class="nav-link">Token Redemption</a>
            <a href="admins.php" class="nav-link">Administrators</a>
            <a href="roles.php" class="nav-link">Roles & Permissions</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <h3>Welcome back, Admin</h3>
            <div style="color: #6b7280;"><?php echo date('F j, Y'); ?></div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Beneficiaries</h4>
                <p>12,450</p>
            </div>
            <div class="stat-card">
                <h4>Tokens Issued</h4>
                <p>8,200</p>
            </div>
            <div class="stat-card">
                <h4>Tokens Redeemed</h4>
                <p>5,120</p>
            </div>
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <h4>SMS Balance</h4>
                <p>₦ 45,000</p>
            </div>
        </div>

        <div class="card">
            <h4>Recent Redemptions</h4>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Beneficiary</th>
                            <th>Program</th>
                            <th>Token</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>John Doe</td>
                            <td>Fertilizer Support</td>
                            <td><code>AF78X2</code></td>
                            <td><span class="badge badge-success">Redeemed</span></td>
                            <td>2 mins ago</td>
                        </tr>
                        <tr>
                            <td>Alice Smith</td>
                            <td>Seed Distribution</td>
                            <td><code>BF90Y1</code></td>
                            <td><span class="badge badge-warning">Unused</span></td>
                            <td>1 hour ago</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
