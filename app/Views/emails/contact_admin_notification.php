<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>New Account Access Request</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0; 
            padding: 0; 
            background-color: #f8f9fa;
        }
        .container { 
            max-width: 700px; 
            margin: 20px auto; 
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header { 
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .content { 
            padding: 40px 30px; 
            background: white;
        }
        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #0d6efd;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #495057;
        }
        .info-value {
            flex: 1;
            color: #212529;
        }
        .reason-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
        }
        .btn-primary {
            background: #0d6efd;
            color: white;
        }
        .btn-success {
            background: #198754;
            color: white;
        }
        .footer { 
            text-align: center; 
            padding: 30px 20px; 
            background: #f8f9fa;
            color: #6c757d; 
            font-size: 14px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔔 New Account Access Request</h1>
            <p>WeBuild WITMS - IT Administrator Alert</p>
        </div>
        
        <div class='content'>
            <h2>Account Access Request Details</h2>
            
            <div class='info-section'>
                <h4>👤 Personal Information</h4>
                <div class='info-row'>
                    <div class='info-label'>Full Name:</div>
                    <div class='info-value'><?= esc($fullName) ?></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Email:</div>
                    <div class='info-value'><?= esc($requestData['email']) ?></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Phone:</div>
                    <div class='info-value'><?= esc($requestData['phone'] ?: 'Not provided') ?></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Employee ID:</div>
                    <div class='info-value'><?= esc($requestData['employee_id'] ?: 'Not provided') ?></div>
                </div>
            </div>

            <div class='info-section'>
                <h4>🏢 Work Information</h4>
                <div class='info-row'>
                    <div class='info-label'>Department:</div>
                    <div class='info-value'><?= esc($departmentName) ?></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Requested Role:</div>
                    <div class='info-value'>
                        <?= esc($roleName) ?>
                        <?php if (!empty($roleDescription)): ?>
                            <br><small style='color: #6c757d;'><?= esc($roleDescription) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class='reason-section'>
                <h4>📝 Reason for Access Request</h4>
                <p><?= esc($requestData['reason']) ?></p>
            </div>

            <div class='info-section'>
                <h4>🔍 Request Metadata</h4>
                <div class='info-row'>
                    <div class='info-label'>Submitted:</div>
                    <div class='info-value'><?= esc($requestData['requested_at']) ?></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>IP Address:</div>
                    <div class='info-value'><?= esc($requestData['ip_address']) ?></div>
                </div>
            </div>

            <div class='action-buttons'>
                <a href='mailto:<?= esc($requestData['email']) ?>?subject=WeBuild WITMS Account - Follow Up' class='btn btn-primary'>
                    📧 Contact Requester
                </a>
                <a href='<?= base_url('dashboard/it-administrator') ?>' class='btn btn-success'>
                    🏗️ Access Admin Panel
                </a>
            </div>

            <p><strong>Next Steps:</strong></p>
            <ol>
                <li>Review the request details above</li>
                <li>Verify the requester's identity and authorization</li>
                <li>Create the user account in WITMS if approved</li>
                <li>Assign appropriate role: <strong><?= esc($roleName) ?></strong></li>
                <li>Assign to department: <strong><?= esc($departmentName) ?></strong></li>
                <li>Send login credentials to the new user</li>
                <li>Contact the requester with the account status</li>
            </ol>
        </div>
        
        <div class='footer'>
            <p><strong>WeBuild Company</strong> - IT Department</p>
            <p>This is an automated notification from the WITMS system.</p>
            <p>Please process this request within 24 hours during business days.</p>
        </div>
    </div>
</body>
</html>
