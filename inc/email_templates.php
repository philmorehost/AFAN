<?php
/**
 * AFAN Email Templates
 */

function get_email_template($title, $content) {
    $year = date('Y');
    $appName = defined('APP_NAME') ? APP_NAME : 'AFAN Food Security Platform';

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { background-color: #f3f4f6; padding: 40px 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .header { background-color: #059669; padding: 40px 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 28px; font-weight: 700; letter-spacing: -0.025em; }
        .content { padding: 40px; line-height: 1.6; color: #374151; }
        .content h2 { color: #111827; margin-top: 0; font-size: 20px; font-weight: 600; }
        .footer { background-color: #f9fafb; padding: 25px; text-align: center; color: #6b7280; font-size: 13px; border-top: 1px solid #e5e7eb; }
        .alert-box { background-color: #fff7ed; border-left: 4px solid #f97316; padding: 15px; margin: 20px 0; border-radius: 0 4px 4px 0; }
        .token-badge { display: inline-block; background-color: #ecfdf5; color: #065f46; padding: 12px 24px; border-radius: 8px; font-family: monospace; font-size: 24px; font-weight: 700; letter-spacing: 2px; margin: 20px 0; border: 1px dashed #10b981; }
        .btn { display: inline-block; padding: 12px 28px; background-color: #059669; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 25px; }
        .data-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
        .data-label { color: #6b7280; font-weight: 500; }
        .data-value { color: #111827; font-weight: 600; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>AFAN FISP</h1>
            </div>
            <div class="content">
                <h2>{$title}</h2>
                {$content}
            </div>
            <div class="footer">
                <p>&copy; {$year} All Farmers Association of Nigeria (AFAN).<br>Food Security Platform - Strategic Agricultural Management</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
}
