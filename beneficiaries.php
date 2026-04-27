<?php
/**
 * AFAN Beneficiaries Management
 */
require_once 'inc/init.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$beneficiaryService = new BeneficiaryService($db);
$message = '';
$error = '';

// Handle NIN Verification & Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_nin'])) {
    $nin = $_POST['nin'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';

    $ninData = $beneficiaryService->verifyNIN($nin);

    if ($ninData && $ninData['status'] === 'success') {
        $data = $ninData['data'];

        // Prepare data for registration
        // Note: Datagifting API uses 'lastname' for the surname field
        $regData = [
            'name' => $data['firstname'] . ' ' . ($data['lastname'] ?? $data['surname'] ?? ''),
            'phone' => $phone ?: ($data['phone'] ?? ''),
            'email' => $email,
            'nin' => $nin,
            'gender' => $data['gender'],
            'state_of_origin' => $data['state_of_origin'],
            'photo' => $data['photo']
        ];

        if ($beneficiaryService->register($regData)) {
            $message = "Beneficiary registered successfully after NIN verification.";
        } else {
            $error = "NIN verified but registration failed (possibly already exists).";
        }
    } else {
        $error = "NIN Verification Failed: " . ($ninData['message'] ?? 'Unknown error');
    }
}

$beneficiaries = $beneficiaryService->getAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiaries - AFAN Platform</title>
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

        .table-container { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        th, td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        th { color: #6b7280; font-weight: 600; }
        .farmer-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background: #e5e7eb;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            width: 100%;
            max-width: 500px;
        }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.4rem; font-size: 0.875rem; color: #4b5563; }
        input {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #d1d5db;
            border-radius: 0.4rem;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>AFAN FISP</h2>
        <nav>
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="beneficiaries.php" class="nav-link active">Beneficiaries</a>
            <a href="programs.php" class="nav-link">Support Programs</a>
            <a href="tokens.php" class="nav-link">Token Redemption</a>
            <a href="admins.php" class="nav-link">Administrators</a>
            <a href="roles.php" class="nav-link">Roles & Permissions</a>
            <a href="audit.php" class="nav-link">Audit Trail</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3 style="margin: 0;">Beneficiaries</h3>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='flex'">+ Register Beneficiary</button>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>NIN</th>
                            <th>Phone</th>
                            <th>State</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($beneficiaries as $b): ?>
                            <tr>
                                <td>
                                    <?php if ($b['photo']): ?>
                                        <img src="data:image/jpeg;base64,<?php echo $b['photo']; ?>" class="farmer-img">
                                    <?php else: ?>
                                        <div class="farmer-img"></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($b['name']); ?></td>
                                <td><code><?php echo htmlspecialchars($b['nin']); ?></code></td>
                                <td><?php echo htmlspecialchars($b['phone']); ?></td>
                                <td><?php echo htmlspecialchars($b['state_of_origin'] ?? 'N/A'); ?></td>
                                <td><span style="color: #059669; font-weight: 600;">Verified</span></td>
                                <td><?php echo date('M j, Y', strtotime($b['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Beneficiary Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h4 style="margin-top: 0;">Verify & Register Beneficiary</h4>
            <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1.5rem;">Enter the farmer's NIN to fetch data from the national identity database.</p>
            <form method="POST">
                <div class="form-group">
                    <label>NIN (11 Digits)</label>
                    <input type="text" name="nin" pattern="\d{11}" required placeholder="e.g. 12345678901">
                </div>
                <div class="form-group">
                    <label>Additional Phone (Optional)</label>
                    <input type="text" name="phone">
                </div>
                <div class="form-group">
                    <label>Email Address (Optional)</label>
                    <input type="email" name="email">
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="verify_nin" class="btn btn-primary" style="flex: 1;">Verify & Register</button>
                    <button type="button" class="btn" style="background: #e5e7eb; flex: 1;" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
