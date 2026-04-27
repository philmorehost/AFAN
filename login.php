<?php
/**
 * AFAN Admin Login
 */
require_once 'inc/init.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "CSRF verification failed.";
    } else {
        $adminService = new AdminService($db);
        $user = $adminService->authenticate($username, $password);

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = $user['role_id'];

        // Log activity
        log_audit($user['id'], 'login', 'User logged in successfully');

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AFAN Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --secondary: #10b981;
            --dark: #111827;
            --light: #f3f4f6;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: white;
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-card h2 {
            margin-top: 0;
            color: var(--dark);
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4b5563;
            font-size: 0.875rem;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }
        input:focus {
            border-color: var(--primary);
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: var(--secondary);
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>AFAN FISP Login</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php csrf_field(); ?>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Sign In</button>
        </form>

        <p style="text-align: center; margin-top: 2rem; font-size: 0.875rem; color: #6b7280;">
            <a href="index.php" style="color: var(--primary); text-decoration: none;">&larr; Back to Landing Page</a>
        </p>
    </div>
</body>
</html>
