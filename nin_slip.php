<?php
/**
 * AFAN Digital NIN Slip
 */
require_once 'inc/init.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM beneficiaries WHERE id = ?");
$stmt->execute([$id]);
$b = $stmt->fetch();

if (!$b) {
    die("Beneficiary not found.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIN Slip - <?php echo htmlspecialchars($b['name']); ?></title>
    <style>
        body { font-family: sans-serif; background: #f3f4f6; margin: 0; padding: 2rem; }
        .slip-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border: 2px solid #059669;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .slip-header {
            background: #059669;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .slip-body {
            padding: 2rem;
            display: flex;
            gap: 2rem;
        }
        .photo-box {
            width: 150px;
            height: 180px;
            background: #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        .info-box { flex: 1; }
        .info-item { margin-bottom: 1rem; }
        .label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 700; }
        .value { font-size: 1.1rem; color: #111827; font-weight: 600; }
        .nin-box {
            background: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            margin-top: 1rem;
            border: 1px dashed #d1d5db;
        }
        .nin-value { font-size: 1.5rem; letter-spacing: 0.2rem; color: #059669; font-weight: 700; }

        .no-print { text-align: center; margin-bottom: 1rem; }
        .btn-print { background: #059669; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }

        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .slip-container { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print Digital Slip</button>
        <a href="beneficiaries.php" style="margin-left: 1rem; color: #6b7280; text-decoration: none;">&larr; Back</a>
    </div>

    <div class="slip-container">
        <div class="slip-header">
            <h2 style="margin: 0;">AFAN - FISP</h2>
            <p style="margin: 0.5rem 0 0; font-size: 0.875rem;">Farmer Verification Slip</p>
        </div>
        <div class="slip-body">
            <div class="photo-box">
                <?php if ($b['photo']): ?>
                    <img src="data:image/jpeg;base64,<?php echo $b['photo']; ?>">
                <?php endif; ?>
            </div>
            <div class="info-box">
                <div class="info-item">
                    <div class="label">Full Name</div>
                    <div class="value"><?php echo htmlspecialchars($b['name']); ?></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="info-item">
                        <div class="label">Gender</div>
                        <div class="value"><?php echo htmlspecialchars($b['gender'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">State of Origin</div>
                        <div class="value"><?php echo htmlspecialchars($b['state_of_origin'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Phone Number</div>
                    <div class="value"><?php echo htmlspecialchars($b['phone']); ?></div>
                </div>
                <div class="nin-box">
                    <div class="label">National Identification Number</div>
                    <div class="nin-value"><?php echo htmlspecialchars($b['nin']); ?></div>
                </div>
            </div>
        </div>
        <div style="padding: 1rem; background: #f3f4f6; text-align: center; font-size: 0.75rem; color: #9ca3af;">
            Verified via National Identity Database (Datagifting API) on <?php echo date('Y-m-d', strtotime($b['created_at'])); ?>
        </div>
    </div>
</body>
</html>
