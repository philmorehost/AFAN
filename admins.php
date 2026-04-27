<?php
/**
 * AFAN Administrator Management
 */
require_once 'inc/init.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$adminService = new AdminService($db);

$roles = $adminService->getRoles();
$admins = $adminService->getAdmins();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email'], 'email');
    $password = $_POST['password'];
    $role_id = (int)$_POST['role_id'];

    if ($adminService->createAdmin($username, $email, $password, $role_id)) {
        $message = "Administrator created successfully!";
        $admins = $adminService->getAdmins(); // Refresh list
    } else {
        $error = "Error creating administrator.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrators - AFAN Platform</title>
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
        .sidebar h2 { font-size: 1.25rem; margin-bottom: 2rem; color: var(--secondary); text-align: center; }
        .nav-link { display: block; padding: 0.75rem 1rem; color: #9ca3af; text-decoration: none; border-radius: 0.5rem; margin-bottom: 0.5rem; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { background: #1f2937; color: white; }
        
        .main-content { padding: 1.5rem; width: 100%; box-sizing: border-box; }
        @media (min-width: 768px) { .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 2rem; } }
        
        .card { background: var(--card-bg); border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 2rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: #4b5563; }
        input, select { width: 100%; padding: 0.6rem; border: 1px solid #d1d5db; border-radius: 0.4rem; box-sizing: border-box; }
        .btn { background: var(--primary); color: white; padding: 0.6rem 1.2rem; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .btn:hover { background: var(--secondary); }
        
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }

        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th, td { text-align: left; padding: 1rem; border-bottom: 1px solid #f3f4f6; }
        th { color: #6b7280; font-weight: 600; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #d1fae5; color: #065f46; }
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
            <a href="admins.php" class="nav-link active">Administrators</a>
            <a href="roles.php" class="nav-link">Roles & Permissions</a>
            <a href="audit.php" class="nav-link">Audit Trail</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header style="margin-bottom: 2rem;">
            <h3 style="margin: 0;">Administrators</h3>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <h4 style="margin-top: 0;">Add New Administrator</h4>
            <form method="POST">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role_id" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" name="create_admin" class="btn" style="margin-top: 1rem;">Create Admin Account</button>
            </form>
        </div>

        <div class="card">
            <h4>Existing Administrators</h4>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($admin['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td><span class="badge"><?php echo htmlspecialchars($admin['role_name'] ?? 'No Role'); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
