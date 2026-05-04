<?php
/**
 * AFAN Role & Permission Management
 */
require_once 'inc/init.php';

$adminService = new AdminService($db);

// Predefined Permission Keys
$available_permissions = [
    'all' => 'Super Access (All Permissions)',
    'manage_beneficiaries' => 'Manage Beneficiaries (Add/Edit/CSV)',
    'manage_tokens' => 'Manage Tokens (Issue/Redeem)',
    'manage_admins' => 'Manage Administrators & Roles',
    'view_reports' => 'View System Reports',
    'view_audit' => 'View Audit Trails'
];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_role'])) {
    $role_name = sanitize($_POST['role_name']);
    $permissions = $_POST['permissions'] ?? [];

    if ($adminService->createRole($role_name, $permissions)) {
        $message = "Role created successfully!";
    } else {
        $message = "Error creating role.";
    }
}

$roles = $adminService->getRoles();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles & Permissions - AFAN Platform</title>
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
        
        .sidebar { width: 100%; background: var(--dark); color: white; padding: 1rem; }
        @media (min-width: 768px) { .sidebar { width: 260px; height: 100vh; position: fixed; padding: 2rem 1rem; } }
        .sidebar h2 { font-size: 1.25rem; margin-bottom: 2rem; color: var(--secondary); text-align: center; }
        .nav-link { display: block; padding: 0.75rem 1rem; color: #9ca3af; text-decoration: none; border-radius: 0.5rem; margin-bottom: 0.5rem; }
        .nav-link:hover, .nav-link.active { background: #1f2937; color: white; }

        .main-content { padding: 1.5rem; width: 100%; }
        @media (min-width: 768px) { .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 2rem; } }

        .card { background: var(--card-bg); border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.75rem; font-weight: 600; }
        input[type="text"] { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; }
        
        .permission-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; }
        .permission-item { display: flex; align-items: center; gap: 0.5rem; background: #f3f4f6; padding: 0.75rem; border-radius: 0.5rem; }
        .permission-item input { width: auto; }
        
        .btn { background: var(--primary); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; width: 100%; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: 1rem; border-bottom: 1px solid #f3f4f6; }
        th { color: #6b7280; font-weight: 600; }
        .perm-tag { display: inline-block; background: #e5e7eb; padding: 0.2rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; margin: 0.1rem; }
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>AFAN Platform</h2>
        <nav>
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="beneficiaries.php" class="nav-link">Beneficiaries</a>
            <a href="admins.php" class="nav-link">Administrators</a>
            <a href="roles.php" class="nav-link active">Roles & Permissions</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header style="margin-bottom: 2rem;">
            <h3>Role & Permission Management</h3>
        </header>

        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <h4>Create New Role</h4>
            <form method="POST">
                <div class="form-group">
                    <label>Role Name</label>
                    <input type="text" name="role_name" placeholder="e.g. Regional Manager" required>
                </div>
                <div class="form-group">
                    <label>Assign Permissions</label>
                    <div class="permission-grid">
                        <?php foreach ($available_permissions as $key => $label): ?>
                            <div class="permission-item">
                                <input type="checkbox" name="permissions[]" value="<?php echo $key; ?>" id="perm_<?php echo $key; ?>">
                                <label for="perm_<?php echo $key; ?>" style="margin-bottom: 0; font-weight: 400;"><?php echo $label; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" name="create_role" class="btn" style="max-width: 200px;">Create Role</button>
            </form>
        </div>

        <div class="card">
            <h4>Defined Roles</h4>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Role Name</th>
                            <th>Permissions</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): 
                            $perms = json_decode($role['permissions'], true);
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td>
                                    <?php if (!empty($perms)): ?>
                                        <?php foreach ($perms as $p): ?>
                                            <span class="perm-tag"><?php echo htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($role['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
