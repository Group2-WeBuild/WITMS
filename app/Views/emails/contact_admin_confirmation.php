<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Request Received - WeBuild WITMS</title>
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
            max-width: 600px; 
            margin: 20px auto; 
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header { 
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .content { 
            padding: 40px 30px; 
            background: white;
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #0d6efd;
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
            <h1>✅ Request Received Successfully</h1>
            <p>WeBuild WITMS - Account Access Request</p>
        </div>
        
        <div class='content'>
            <div class='success-box'>
                <h3>Thank You, <?= esc($requestData['first_name']) ?>!</h3>
                <p>Your account access request has been received and is being reviewed by our IT Administrator.</p>
            </div>

            <p>Hello <strong><?= esc($fullName) ?></strong>,</p>
            
            <p>We have successfully received your request for access to the WeBuild Warehouse Inventory and Tracking Management System (WITMS).</p>

            <div class='info-box'>
                <h4>📋 Your Request Summary</h4>
                <p>
                    <strong>Full Name:</strong> <?= esc($fullName) ?><br>
                    <strong>Email:</strong> <?= esc($requestData['email']) ?><br>
                    <strong>Department:</strong> <?= esc($departmentName) ?><br>
                    <strong>Requested Role:</strong> <?= esc($roleName) ?><br>
                    <strong>Submitted:</strong> <?= esc($requestData['requested_at']) ?>
                </p>
            </div>

            <h4>🕐 What Happens Next?</h4>
            <ol>
                <li><strong>Review Process:</strong> Our IT Administrator will review your request within 24 hours during business days</li>
                <li><strong>Verification:</strong> We may contact you to verify your identity and authorization</li>
                <li><strong>Account Creation:</strong> If approved, your account will be created with appropriate permissions</li>
                <li><strong>Credentials:</strong> You'll receive your login credentials via email</li>
                <li><strong>Welcome:</strong> We'll provide system orientation and training resources</li>
            </ol>

            <div class='info-box'>
                <h4>📞 Need Immediate Assistance?</h4>
                <p>If you have urgent questions or need immediate assistance, please contact our IT support team:</p>
                <p>
                    <strong>Email:</strong> <a href='mailto:it-support@webuild.com'>it-support@webuild.com</a><br>
                    <strong>Phone:</strong> (123) 456-7890 ext. 2<br>
                    <strong>Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM
                </p>
            </div>

            <p>We appreciate your interest in using our WITMS system and look forward to providing you with access soon.</p>
            
            <p>
                Best regards,<br>
                <strong>WeBuild IT Support Team</strong><br>
                Information Technology Department
            </p>
        </div>
        
        <div class='footer'>
            <p><strong>WeBuild Company</strong> - Building Excellence Together</p>
            <p>This is an automated confirmation email. Please do not reply directly to this message.</p>
        </div>
    </div>
</body>
</html>
