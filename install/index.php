<?php
/**
 * AFAN Platform Installer
 */

define('INSTALLING', true);
session_start();

$stage = isset($_GET['stage']) ? (int)$_GET['stage'] : 1;

// Redirect to Stage 1 if session is lost and they are further ahead
if ($stage > 1 && !isset($_SESSION['install_data'])) {
    // header("Location: index.php?stage=1");
    // exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFAN Installer - Stage <?php echo $stage; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --bg: #f3f4f6;
            --card: #ffffff;
            --text: #1f2937;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: var(--card);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--primary-dark);
        }
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .step {
            width: 30px;
            height: 30px;
            background: #e5e7eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
            z-index: 1;
        }
        .step.active {
            background: var(--primary);
            color: white;
        }
        .step.completed {
            background: var(--primary-dark);
            color: white;
        }
        .steps::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-sizing: border-box;
        }
        .btn {
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn:hover {
            background: var(--primary-dark);
        }
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        .check-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .status-ok { color: #059669; }
        .status-fail { color: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>AFAN Platform Setup</h1>
            <p>Stage <?php echo $stage; ?> of 4</p>
        </div>

        <div class="steps">
            <div class="step <?php echo $stage >= 1 ? ($stage > 1 ? 'completed' : 'active') : ''; ?>">1</div>
            <div class="step <?php echo $stage >= 2 ? ($stage > 2 ? 'completed' : 'active') : ''; ?>">2</div>
            <div class="step <?php echo $stage >= 3 ? ($stage > 3 ? 'completed' : 'active') : ''; ?>">3</div>
            <div class="step <?php echo $stage >= 4 ? ($stage > 4 ? 'completed' : 'active') : ''; ?>">4</div>
        </div>

        <?php
        switch ($stage) {
            case 1:
                include 'stages/stage1.php';
                break;
            case 2:
                include 'stages/stage2.php';
                break;
            case 3:
                include 'stages/stage3.php';
                break;
            case 4:
                include 'stages/stage4.php';
                break;
            default:
                include 'stages/stage1.php';
                break;
        }
        ?>
    </div>
</body>
</html>
