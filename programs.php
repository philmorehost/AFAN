<?php
/**
 * AFAN Programs Management
 */
require_once 'inc/init.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$programService = new ProgramService($db);
$message = '';
$error = '';

// Handle Program Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_program'])) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if ($programService->createProgram($name, $description, $start_date, $end_date)) {
        $message = "Program created successfully.";
    } else {
        $error = "Failed to create program.";
    }
}

// Handle Status Update
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $new_status = $_GET['toggle_status'] == 'active' ? 'active' : 'inactive';
    $programService->updateStatus($_GET['id'], $new_status);
    header("Location: programs.php");
    exit;
}

$programs = $programService->getAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programs - AFAN Platform</title>
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
            text-decoration: none;
            display: inline-block;
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

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #f3f4f6; color: #6b7280; }

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
        input, textarea {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #d1d5db;
            border-radius: 0.4rem;
            box-sizing: border-box;
            font-family: inherit;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>AFAN FISP</h2>
        <nav>
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="beneficiaries.php" class="nav-link">Beneficiaries</a>
            <a href="programs.php" class="nav-link active">Support Programs</a>
            <a href="tokens.php" class="nav-link">Token Redemption</a>
            <a href="admins.php" class="nav-link">Administrators</a>
            <a href="roles.php" class="nav-link">Roles & Permissions</a>
            <a href="audit.php" class="nav-link">Audit Trail</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3 style="margin: 0;">Support Programs</h3>
            <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='flex'">+ New Program</button>
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
                            <th>Program Name</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs as $p): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($p['name']); ?></strong><br>
                                    <small style="color: #6b7280;"><?php echo htmlspecialchars($p['description']); ?></small>
                                </td>
                                <td>
                                    <?php if ($p['status'] == 'active'): ?>
                                        <span class="badge badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $p['start_date']; ?></td>
                                <td><?php echo $p['end_date']; ?></td>
                                <td><?php echo date('M j, Y', strtotime($p['created_at'])); ?></td>
                                <td>
                                    <?php if ($p['status'] == 'active'): ?>
                                        <a href="?toggle_status=inactive&id=<?php echo $p['id']; ?>" style="color: #991b1b; font-size: 0.875rem;">Deactivate</a>
                                    <?php else: ?>
                                        <a href="?toggle_status=active&id=<?php echo $p['id']; ?>" style="color: var(--primary); font-size: 0.875rem;">Activate</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Program Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h4 style="margin-top: 0;">Create New Support Program</h4>
            <form method="POST">
                <div class="form-group">
                    <label>Program Name</label>
                    <input type="text" name="name" required placeholder="e.g. Wet Season Fertilizer Subsidy">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" required></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" required>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="create_program" class="btn btn-primary" style="flex: 1;">Create Program</button>
                    <button type="button" class="btn" style="background: #e5e7eb; flex: 1;" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
