<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <style>
        .email-container { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; }
        .header { background-color: #1a365d; color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; background-color: #f8f9fa; }
        .button { display: inline-block; padding: 12px 24px; background-color: #1a365d; color: white; text-decoration: none; border-radius: 5px; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='header'>
            <h1>🏗️ WeBuild Company</h1>
            <p>Warehouse Inventory & Tracking Management System</p>
        </div>
        
        <div class='content'>
            <h2>Password Reset Request</h2>
            <p>Hello <strong><?= esc($fullName) ?></strong>,</p>
            <p>We received a request to reset the password for your WeBuild WITMS account.</p>
            
            <div style='margin: 20px 0;'>
                <strong>Account Details:</strong><br>
                Role: <?= esc($role) ?><br>
                <?php if (!empty($employeeId)): ?>
                Employee ID: <?= esc($employeeId) ?><br>
                <?php endif; ?>
                Request Time: <?= date('M d, Y H:i:s') ?>
            </div>
            
            <p>Click the button below to reset your password:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='<?= esc($resetLink) ?>' class='button'>Reset Password</a>
            </p>
            
            <div class='warning'>
                <strong>⚠️ Security Notice:</strong>
                <ul>
                    <li>This link will expire in <strong>1 hour</strong></li>
                    <li>If you didn't request this reset, please ignore this email</li>
                    <li>Never share this link with anyone</li>
                    <li>Contact IT Administrator if you have concerns</li>
                </ul>
            </div>
            
            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <p style='word-break: break-all; color: #666;'><?= esc($resetLink) ?></p>
        </div>
        
        <div class='footer'>
            <p>This is an automated message from WeBuild WITMS System</p>
            <p>© <?= date('Y') ?> WeBuild Construction Company. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
