<?php
/**
 * AFAN Token Management & Redemption
 */
require_once 'inc/init.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$adminService = new AdminService($db);
if (!$adminService->hasPermission($_SESSION['user_id'], 'manage_tokens')) {
    die("Unauthorized access: You do not have permission to manage tokens.");
}

$comm = new CommService($db);
$tokenService = new TokenService($db, $comm);
$programService = new ProgramService($db);
$beneficiaryService = new BeneficiaryService($db);

$message = '';
$error = '';

// Handle Token Issuance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_token'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $beneficiary_id = $_POST['beneficiary_id'] ?? '';
    $program_id = $_POST['program_id'] ?? '';

    $token = $tokenService->issueToken($beneficiary_id, $program_id);
    if ($token) {
        $message = "Token generated and sent to beneficiary: <strong>{$token}</strong>";
    } else {
        $error = "Failed to issue token.";
    }
}

// Handle Token Redemption
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_token'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $code = $_POST['token_code'] ?? '';
    $result = $tokenService->redeemToken($code, $_SESSION['user_id']);

    if ($result['success']) {
        $message = "Token <strong>{$code}</strong> redeemed successfully for {$result['data']['b_name']}.";
    } else {
        $error = $result['message'];
    }
}

$activePrograms = $programService->getActive();
$beneficiaries = $beneficiaryService->getAll();
$redemptions = $tokenService->getRecentRedemptions(20);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tokens - AFAN Platform</title>
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
            box-sizing: border-box;
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
            box-sizing: border-box;
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
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--secondary); }

        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
        }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.4rem; font-size: 0.875rem; color: #4b5563; }
        input, select {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #d1d5db;
            border-radius: 0.4rem;
            box-sizing: border-box;
        }

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
        .badge-success { background: #d1fae5; color: #065f46; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>AFAN FISP</h2>
        <nav>
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="beneficiaries.php" class="nav-link">Beneficiaries</a>
            <a href="programs.php" class="nav-link">Support Programs</a>
            <a href="tokens.php" class="nav-link active">Token Redemption</a>
            <a href="admins.php" class="nav-link">Administrators</a>
            <a href="roles.php" class="nav-link">Roles & Permissions</a>
            <a href="settings.php" class="nav-link">System Settings</a>
            <a href="cms_landing.php" class="nav-link">Landing Page CMS</a>
            <a href="pages.php" class="nav-link">Custom Pages</a>
            <a href="audit.php" class="nav-link">Audit Trail</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header style="margin-bottom: 2rem;">
            <h3 style="margin: 0;">Token Management</h3>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-grid">
            <!-- Issue Token -->
            <div class="card">
                <h4 style="margin-top: 0;">Issue New Token</h4>
                <form method="POST">
                    <?php csrf_field(); ?>
                    <div class="form-group">
                        <label>Beneficiary</label>
                        <select name="beneficiary_id" required>
                            <option value="">Select Farmer...</option>
                            <?php foreach ($beneficiaries as $b): ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?> (<?php echo $b['nin']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Program</label>
                        <select name="program_id" required>
                            <option value="">Select Program...</option>
                            <?php foreach ($activePrograms as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="issue_token" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Generate & Send Token</button>
                </form>
            </div>

            <!-- Redeem Token -->
            <div class="card" style="border-top: 4px solid #f59e0b;">
                <h4 style="margin-top: 0;">Redeem Token</h4>
                <form method="POST">
                    <?php csrf_field(); ?>
                    <div class="form-group">
                        <label>Token Code</label>
                        <input type="text" name="token_code" placeholder="e.g. AF78X2" required style="font-family: monospace; font-size: 1.25rem; text-align: center; text-transform: uppercase;">
                    </div>
                    <button type="submit" name="redeem_token" class="btn" style="width: 100%; margin-top: 1rem; background: #f59e0b; color: white;">Verify & Redeem</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h4>Recent Redemption History</h4>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Beneficiary</th>
                            <th>Program</th>
                            <th>Token</th>
                            <th>Status</th>
                            <th>Redeemed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($redemptions as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['beneficiary_name']); ?></td>
                                <td><?php echo htmlspecialchars($r['program_name']); ?></td>
                                <td><code><?php echo htmlspecialchars($r['token_code']); ?></code></td>
                                <td><span class="badge-success">Redeemed</span></td>
                                <td><?php echo date('M j, Y g:i a', strtotime($r['redeemed_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($redemptions)): ?>
                            <tr><td colspan="5" style="text-align: center; color: #9ca3af;">No redemptions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
