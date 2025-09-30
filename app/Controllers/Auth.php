<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\DepartmentModel;

class Auth extends BaseController
{
    protected $userModel;
    protected $departmentModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->departmentModel = new DepartmentModel();
        helper(['form', 'url']);
    }

    /**
     * Display login page or handle login authentication
     */
    public function login()
    {
        // Redirect if already logged in
        if (session()->get('isLoggedIn')) {
            return $this->redirectToDashboard(session()->get('user_role'));
        }

        // Handle login form submission
        if ($this->request->getMethod() === 'POST') {
            return $this->handleLogin();
        }

        // Display login form
        $data = [
            'title' => 'Sign In - WeBuild WITMS',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/login', $data);
    }

    /**
     * Handle login authentication process
     */
    private function handleLogin()
    {
        // Validation rules
        $rules = [
            'email' => [
                'label' => 'Email Address',
                'rules' => 'required|valid_email'
            ],
            'password' => [
                'label' => 'Password', 
                'rules' => 'required|min_length[3]'
            ]
        ];

        // Validate input
        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Please check your input and try again.');
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        try {
            // Find user by email
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Email address not found in WeBuild system.');
            }

            // Check if user account is active
            if (!$user['is_active']) {
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Your account has been deactivated. Please contact IT Administrator.');
            }

            // Verify password
            if (!$this->userModel->verifyPassword($password, $user['password'])) {
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Invalid password. Please try again.');
            }

            // Get department information
            $department = null;
            if (!empty($user['department_id'])) {
                $department = $this->departmentModel->find($user['department_id']);
            }

            // Prepare session data based on user role
            $sessionData = $this->prepareSessionData($user, $department);

            // Set session data
            session()->set($sessionData);

            // Update last login timestamp
            $this->userModel->updateLastLogin($user['id']);

            // Set remember me cookie if requested
            if ($remember) {
                $this->setRememberMeCookie($user['id']);
            }

            // Log successful login
            log_message('info', "User {$user['email']} ({$user['role']}) logged in successfully");

            // Redirect to appropriate dashboard based on role
            return $this->redirectToDashboard($user['role']);

        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'An error occurred during login. Please try again.');
        }
    }

    /**
     * Prepare comprehensive session data based on user role
     */
    private function prepareSessionData(array $user, ?array $department): array
    {
        $fullName = $this->userModel->getFullName($user);
        
        // Base session data for all users
        $sessionData = [
            'isLoggedIn' => true,
            'user_id' => $user['id'],
            'user_email' => $user['email'],
            'user_role' => $user['role'],
            'first_name' => $user['first_name'],
            'middle_name' => $user['middle_name'],
            'last_name' => $user['last_name'],
            'full_name' => $fullName,
            'phone_number' => $user['phone_number'],
            'department_id' => $user['department_id'],
            'department_name' => $department['name'] ?? null,
            'department_location' => $department['warehouse_location'] ?? null,
            'login_time' => date('Y-m-d H:i:s'),
            'last_activity' => time()
        ];

        // Add role-specific permissions and data
        switch ($user['role']) {
            case 'Warehouse Manager':
                $sessionData = array_merge($sessionData, [
                    'permissions' => [
                        'warehouse_access' => true,
                        'management_access' => true,
                        'financial_access' => false,
                        'user_management' => true,
                        'inventory_management' => true,
                        'staff_supervision' => true,
                        'reports_access' => true
                    ],
                    'dashboard_type' => 'warehouse_manager',
                    'can_approve_requests' => true,
                    'can_manage_staff' => true,
                    'access_level' => 'manager'
                ]);
                break;

            case 'Warehouse Staff':
                $sessionData = array_merge($sessionData, [
                    'permissions' => [
                        'warehouse_access' => true,
                        'management_access' => false,
                        'financial_access' => false,
                        'inventory_entry' => true,
                        'stock_movements' => true,
                        'basic_reports' => true
                    ],
                    'dashboard_type' => 'warehouse_staff',
                    'can_edit_inventory' => true,
                    'access_level' => 'staff'
                ]);
                break;

            case 'Inventory Auditor':
                $sessionData = array_merge($sessionData, [
                    'permissions' => [
                        'warehouse_access' => true,
                        'management_access' => false,
                        'financial_access' => false,
                        'audit_access' => true,
                        'inventory_verification' => true,
                        'discrepancy_reports' => true,
                        'audit_reports' => true
                    ],
                    'dashboard_type' => 'inventory_auditor',
                    'can_perform_audits' => true,
                    'access_level' => 'auditor'
                ]);
                break;

            case 'Procurement Officer':
                $sessionData = array_merge($sessionData, [
                    'permissions' => [
                        'warehouse_access' => true,
                        'management_access' => false,
                        'financial_access' => false,
                        'procurement_access' => true,
                        'supplier_management' => true,
                        'purchase_orders' => true,
                        'procurement_reports' => true
                    ],
                    'dashboard_type' => 'procurement_officer',
                    'can_create_orders' => true,
                    'can_manage_suppliers' => true,
                    'access_level' => 'officer'
                ]);
                break;

            case 'Accounts Payable Clerk':
                $sessionData = array_merge($sessionData, [
                    'permissions' => [
                        'warehouse_access' => false,
                        'management_access' => false,
                        'financial_access' => true,
                        'accounts_payable' => true,
                        'invoice_processing' => true,
                        'payment_processing' => true,
                        'financial_reports' => true
                    ],
                    'dashboard_type' => 'accounts_payable',
                    'can_process_payments' => true,
                    'access_level' => 'clerk'
                ]);
                break;

            case 'Accounts Receivable Clerk':
                $sessionData = array_merge($sessionData, [
                    'permissions' => [
                        'warehouse_access' => false,
                        'management_access' => false,
                        'financial_access' => true,
                        'accounts_receivable' => true,
                        'billing_management' => true,
                        'collection_management' => true,
                        'customer_accounts' => true
                    ],
                    'dashboard_type' => 'accounts_receivable',
                    'can_manage_billing' => true,
                    'access_level' => 'clerk'
                ]);
                break;

            case 'IT Administrator':
                $sessionData = array_merge($sessionData, [
                    'permissions' => [
                        'warehouse_access' => true,
                        'management_access' => true,
                        'financial_access' => true,
                        'system_administration' => true,
                        'user_management' => true,
                        'system_configuration' => true,
                        'backup_management' => true,
                        'security_management' => true,
                        'all_reports' => true
                    ],
                    'dashboard_type' => 'it_administrator',
                    'can_manage_users' => true,
                    'can_configure_system' => true,
                    'can_access_all_modules' => true,
                    'access_level' => 'administrator'
                ]);
                break;

            case 'Top Management':
                $sessionData = array_merge($sessionData, [
                    'permissions' => [
                        'warehouse_access' => true,
                        'management_access' => true,
                        'financial_access' => true,
                        'executive_access' => true,
                        'strategic_reports' => true,
                        'company_overview' => true,
                        'performance_analytics' => true,
                        'decision_making' => true
                    ],
                    'dashboard_type' => 'top_management',
                    'can_view_all_data' => true,
                    'can_make_strategic_decisions' => true,
                    'access_level' => 'executive'
                ]);
                break;

            default:
                // Default minimal permissions for unknown roles
                $sessionData = array_merge($sessionData, [
                    'permissions' => [
                        'warehouse_access' => false,
                        'management_access' => false,
                        'financial_access' => false
                    ],
                    'dashboard_type' => 'default',
                    'access_level' => 'basic'
                ]);
        }

        return $sessionData;
    }

    /**
     * Redirect user to appropriate dashboard based on role
     */
    private function redirectToDashboard(string $role)
    {
        $dashboardRoutes = [
            'Warehouse Manager' => '/dashboard/warehouse-manager',
            'Warehouse Staff' => '/dashboard/warehouse-staff',
            'Inventory Auditor' => '/dashboard/inventory-auditor',
            'Procurement Officer' => '/dashboard/procurement-officer',
            'Accounts Payable Clerk' => '/dashboard/accounts-payable',
            'Accounts Receivable Clerk' => '/dashboard/accounts-receivable',
            'IT Administrator' => '/dashboard/it-administrator',
            'Top Management' => '/dashboard/top-management'
        ];

        $route = $dashboardRoutes[$role] ?? '/dashboard';
        
        return redirect()->to($route)->with('success', 'Welcome back to WeBuild WITMS!');
    }

    /**
     * Set remember me cookie
     */
    private function setRememberMeCookie(int $userId)
    {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store token in database (you'd need a remember_tokens table)
        // For now, just set a simple cookie
        setcookie('remember_token', $token, $expiry, '/', '', false, true);
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $userEmail = session()->get('user_email');
        $userRole = session()->get('user_role');
        
        // Log logout activity
        log_message('info', "User {$userEmail} ({$userRole}) logged out");
        
        // Destroy session
        session()->destroy();
        
        // Clear remember me cookie
        setcookie('remember_token', '', time() - 3600, '/');
        
        return redirect()->to('/auth/login')->with('success', 'You have been logged out successfully.');
    }    /**
     * Handle password reset request
     */
    public function resetPassword()
    {
        // Handle password reset form submission
        if ($this->request->getMethod() === 'POST') {
            return $this->handlePasswordReset();
        }

        // Display password reset form
        $data = [
            'title' => 'Reset Password - WeBuild WITMS',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/reset_password', $data);
    }

    /**
     * Handle password reset form submission
     */
    private function handlePasswordReset()
    {
        // Validation rules
        $rules = [
            'email' => [
                'label' => 'Email Address',
                'rules' => 'required|valid_email'
            ],
            'employee_id' => [
                'label' => 'Employee ID',
                'rules' => 'permit_empty|alpha_numeric'
            ]
        ];

        // Validate input
        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Please check your input and try again.');
        }

        $email = $this->request->getPost('email');
        $employeeId = $this->request->getPost('employee_id');

        try {
            // Find user by email
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                // Don't reveal if email exists for security reasons
                return redirect()->back()
                               ->with('success', 'If this email is registered in our system, you will receive password reset instructions.');
            }

            // Check if user account is active
            if (!$user['is_active']) {
                return redirect()->back()
                               ->with('error', 'This account is deactivated. Please contact IT Administrator for assistance.');
            }

            // Generate password reset token
            $resetToken = $this->generateResetToken();
            $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

            // Store reset token in database (we'll add this to user model)
            $this->userModel->setPasswordResetToken($user['id'], $resetToken, $resetExpiry);

            // Send password reset email
            $this->sendPasswordResetEmail($user, $resetToken, $employeeId);

            // Log password reset request
            log_message('info', "Password reset requested for user: {$user['email']} (ID: {$user['id']})");

            return redirect()->back()
                           ->with('success', 'Password reset instructions have been sent to your email address. Please check your inbox and spam folder.');

        } catch (\Exception $e) {
            log_message('error', 'Password reset error: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'An error occurred while processing your request. Please try again or contact IT Administrator.');
        }
    }

    /**
     * Generate secure password reset token
     */
    private function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail(array $user, string $token, ?string $employeeId)
    {
        // Email configuration
        $email = \Config\Services::email();
        
        $config = [
            'protocol' => 'mail', // You can change this to 'smtp' for production
            'mailType' => 'html',
            'charset'  => 'utf-8',
            'newline'  => "\r\n"
        ];
        
        $email->initialize($config);

        // Email content
        $resetLink = base_url("auth/reset-password-confirm/{$token}");
        $fullName = $this->userModel->getFullName($user);
        
        $subject = 'Password Reset Request - WeBuild WITMS';
        
        $message = $this->buildPasswordResetEmailTemplate($fullName, $resetLink, $user['role'], $employeeId);

        // Set email parameters
        $email->setFrom('noreply@webuild.com', 'WeBuild WITMS System');
        $email->setTo($user['email']);
        $email->setSubject($subject);
        $email->setMessage($message);

        // Send email
        if ($email->send()) {
            log_message('info', "Password reset email sent to: {$user['email']}");
        } else {
            log_message('error', "Failed to send password reset email to: {$user['email']}");
            // For development, we'll just log the reset link
            log_message('info', "Password reset link for {$user['email']}: {$resetLink}");
        }
    }

    /**
     * Build password reset email template
     */
    private function buildPasswordResetEmailTemplate(string $fullName, string $resetLink, string $role, ?string $employeeId): string
    {
        return "
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
                    <p>Hello <strong>{$fullName}</strong>,</p>
                    <p>We received a request to reset the password for your WeBuild WITMS account.</p>
                    
                    <div style='margin: 20px 0;'>
                        <strong>Account Details:</strong><br>
                        Role: {$role}<br>
                        " . ($employeeId ? "Employee ID: {$employeeId}<br>" : "") . "
                        Request Time: " . date('M d, Y H:i:s') . "
                    </div>
                    
                    <p>Click the button below to reset your password:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetLink}' class='button'>Reset Password</a>
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
                    <p style='word-break: break-all; color: #666;'>{$resetLink}</p>
                </div>
                
                <div class='footer'>
                    <p>This is an automated message from WeBuild WITMS System</p>
                    <p>© " . date('Y') . " WeBuild Construction Company. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Handle password reset confirmation
     */
    public function resetPasswordConfirm(?string $token = null)
    {
        if (!$token) {
            return redirect()->to('/auth/login')
                           ->with('error', 'Invalid password reset link.');
        }

        // Handle password reset confirmation form submission
        if ($this->request->getMethod() === 'POST') {
            return $this->handlePasswordResetConfirm($token);
        }

        // Verify token and show password reset form
        $user = $this->userModel->findByResetToken($token);
        
        if (!$user || $this->userModel->isResetTokenExpired($user['id'])) {
            return redirect()->to('/auth/login')
                           ->with('error', 'Password reset link has expired or is invalid. Please request a new one.');
        }

        $data = [
            'title' => 'Set New Password - WeBuild WITMS',
            'token' => $token,
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        return view('auth/reset_password_confirm', $data);
    }

    /**
     * Handle password reset confirmation form submission
     */
    private function handlePasswordResetConfirm(string $token)
    {
        // Validation rules
        $rules = [
            'password' => [
                'label' => 'New Password',
                'rules' => 'required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/]'
            ],
            'password_confirm' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]'
            ]
        ];

        // Validate input
        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Please check your input and try again.');
        }

        try {
            // Verify token and get user
            $user = $this->userModel->findByResetToken($token);
            
            if (!$user || $this->userModel->isResetTokenExpired($user['id'])) {
                return redirect()->to('/auth/login')
                               ->with('error', 'Password reset link has expired. Please request a new one.');
            }

            $newPassword = $this->request->getPost('password');

            // Update password
            $this->userModel->changePassword($user['id'], $newPassword);

            // Clear reset token
            $this->userModel->clearPasswordResetToken($user['id']);

            // Log password change
            log_message('info', "Password reset completed for user: {$user['email']} (ID: {$user['id']})");

            return redirect()->to('/auth/login')
                           ->with('success', 'Your password has been reset successfully. Please log in with your new password.');

        } catch (\Exception $e) {
            log_message('error', 'Password reset confirmation error: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'An error occurred while resetting your password. Please try again.');
        }
    }    /**
     * Display contact administrator page or handle contact form submission
     */
    public function contactAdministrator()
    {
        // Handle contact form submission
        if ($this->request->getMethod() === 'POST') {
            return $this->handleContactAdminRequest();
        }

        // Display contact admin form
        $data = [
            'title' => 'Contact Administrator - WeBuild WITMS',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/contact_admin', $data);
    }

    /**
     * Handle contact admin form submission
     */
    private function handleContactAdminRequest()
    {
        // Validation rules
        $rules = [
            'first_name' => [
                'label' => 'First Name',
                'rules' => 'required|max_length[100]|alpha_space'
            ],
            'last_name' => [
                'label' => 'Last Name', 
                'rules' => 'required|max_length[100]|alpha_space'
            ],
            'email' => [
                'label' => 'Email Address',
                'rules' => 'required|valid_email|max_length[255]'
            ],
            'phone' => [
                'label' => 'Phone Number',
                'rules' => 'permit_empty|max_length[20]|regex_match[/^[\+]?[0-9\s\-\(\)]+$/]'
            ],
            'department' => [
                'label' => 'Department',
                'rules' => 'required|in_list[construction,project_management,site_supervision,administration,logistics,safety,finance,other]'
            ],
            'role' => [
                'label' => 'Requested Role',
                'rules' => 'required|in_list[worker,supervisor,manager,coordinator,admin,viewer]'
            ],
            'employee_id' => [
                'label' => 'Employee ID',
                'rules' => 'permit_empty|max_length[50]|alpha_numeric_space'
            ],
            'reason' => [
                'label' => 'Reason for Access Request',
                'rules' => 'required|min_length[20]|max_length[1000]'
            ],
            'agreement' => [
                'label' => 'Agreement',
                'rules' => 'required'
            ]
        ];

        // Validate input
        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Please check your input and try again.');
        }

        try {
            // Get form data
            $requestData = [
                'first_name' => $this->request->getPost('first_name'),
                'last_name' => $this->request->getPost('last_name'),
                'email' => $this->request->getPost('email'),
                'phone' => $this->request->getPost('phone'),
                'department' => $this->request->getPost('department'),
                'role' => $this->request->getPost('role'),
                'employee_id' => $this->request->getPost('employee_id'),
                'reason' => $this->request->getPost('reason'),
                'requested_at' => date('Y-m-d H:i:s'),
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ];

            // Send notification email to IT administrators
            $this->sendContactAdminNotification($requestData);

            // Log the request
            log_message('info', "Account access request submitted by: {$requestData['email']} ({$requestData['first_name']} {$requestData['last_name']})");

            return redirect()->back()
                           ->with('success', 'Your request has been submitted successfully! Our IT Administrator will review your request and contact you within 24 hours during business days.');

        } catch (\Exception $e) {
            log_message('error', 'Contact admin request error: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'An error occurred while submitting your request. Please try again or contact IT support directly.');
        }
    }

    /**
     * Send contact admin notification email to IT administrators
     */
    private function sendContactAdminNotification(array $requestData): bool
    {
        try {
            // Get IT administrators
            $itAdministrators = $this->userModel->getITAdministrators();
            
            if (empty($itAdministrators)) {
                log_message('warning', 'No IT administrators found to send contact admin notification');
                return false;
            }

            $email = \Config\Services::email();
            
            // Email subject
            $subject = 'New Account Access Request - WeBuild WITMS';
            
            // Email content
            $message = $this->buildContactAdminEmailTemplate($requestData);

            foreach ($itAdministrators as $admin) {
                $email->clear();
                $email->setFrom(
                    config('Email')->fromEmail, 
                    config('Email')->fromName
                );                $email->setTo($admin['email']);
                $email->setSubject($subject);
                $email->setMessage($message);

                if (!$email->send()) {
                    log_message('error', "Failed to send contact admin notification to: {$admin['email']}");
                }
            }

            // Send confirmation email to requester
            $this->sendContactAdminConfirmation($requestData);

            return true;

        } catch (\Exception $e) {
            log_message('error', "Contact admin notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send confirmation email to the person who submitted the request
     */
    private function sendContactAdminConfirmation(array $requestData): bool
    {
        try {
            $email = \Config\Services::email();
            
            $email->setFrom(
                config('Email')->fromEmail, 
                config('Email')->fromName
            );            $email->setTo($requestData['email']);
            $email->setSubject('Account Access Request Received - WeBuild WITMS');
            $email->setMessage($this->buildContactAdminConfirmationTemplate($requestData));

            return $email->send();

        } catch (\Exception $e) {
            log_message('error', "Contact admin confirmation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build email template for IT administrator notification
     */
    private function buildContactAdminEmailTemplate(array $requestData): string
    {
        $departmentNames = [
            'construction' => 'Construction',
            'project_management' => 'Project Management',
            'site_supervision' => 'Site Supervision',
            'administration' => 'Administration',
            'logistics' => 'Logistics',
            'safety' => 'Safety & Compliance',
            'finance' => 'Finance',
            'other' => 'Other'
        ];

        $roleNames = [
            'worker' => 'Construction Worker',
            'supervisor' => 'Site Supervisor',
            'manager' => 'Project Manager',
            'coordinator' => 'Project Coordinator',
            'admin' => 'Administrative Staff',
            'viewer' => 'View Only Access'
        ];

        $departmentName = $departmentNames[$requestData['department']] ?? $requestData['department'];
        $roleName = $roleNames[$requestData['role']] ?? $requestData['role'];

        return "
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
                            <div class='info-value'>{$requestData['first_name']} {$requestData['last_name']}</div>
                        </div>
                        <div class='info-row'>
                            <div class='info-label'>Email:</div>
                            <div class='info-value'>{$requestData['email']}</div>
                        </div>
                        <div class='info-row'>
                            <div class='info-label'>Phone:</div>
                            <div class='info-value'>" . ($requestData['phone'] ?: 'Not provided') . "</div>
                        </div>
                        <div class='info-row'>
                            <div class='info-label'>Employee ID:</div>
                            <div class='info-value'>" . ($requestData['employee_id'] ?: 'Not provided') . "</div>
                        </div>
                    </div>

                    <div class='info-section'>
                        <h4>🏢 Work Information</h4>
                        <div class='info-row'>
                            <div class='info-label'>Department:</div>
                            <div class='info-value'>{$departmentName}</div>
                        </div>
                        <div class='info-row'>
                            <div class='info-label'>Requested Role:</div>
                            <div class='info-value'>{$roleName}</div>
                        </div>
                    </div>

                    <div class='reason-section'>
                        <h4>📝 Reason for Access Request</h4>
                        <p>{$requestData['reason']}</p>
                    </div>

                    <div class='info-section'>
                        <h4>🔍 Request Metadata</h4>
                        <div class='info-row'>
                            <div class='info-label'>Submitted:</div>
                            <div class='info-value'>{$requestData['requested_at']}</div>
                        </div>
                        <div class='info-row'>
                            <div class='info-label'>IP Address:</div>
                            <div class='info-value'>{$requestData['ip_address']}</div>
                        </div>
                    </div>

                    <div class='action-buttons'>
                        <a href='mailto:{$requestData['email']}?subject=WeBuild WITMS Account - Follow Up' class='btn btn-primary'>
                            📧 Contact Requester
                        </a>
                        <a href='" . base_url('dashboard/it-administrator') . "' class='btn btn-success'>
                            🏗️ Access Admin Panel
                        </a>
                    </div>

                    <p><strong>Next Steps:</strong></p>
                    <ol>
                        <li>Review the request details above</li>
                        <li>Verify the requester's identity and authorization</li>
                        <li>Create the user account in WITMS if approved</li>
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
        ";
    }

    /**
     * Build confirmation email template for the requester
     */
    private function buildContactAdminConfirmationTemplate(array $requestData): string
    {
        return "
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
                        <h3>Thank You, {$requestData['first_name']}!</h3>
                        <p>Your account access request has been received and is being reviewed by our IT Administrator.</p>
                    </div>

                    <p>Hello <strong>{$requestData['first_name']} {$requestData['last_name']}</strong>,</p>
                    
                    <p>We have successfully received your request for access to the WeBuild Warehouse Inventory and Tracking Management System (WITMS).</p>

                    <div class='info-box'>
                        <h4>📋 Your Request Summary</h4>
                        <p><strong>Email:</strong> {$requestData['email']}<br>
                        <strong>Department:</strong> {$requestData['department']}<br>
                        <strong>Requested Role:</strong> {$requestData['role']}<br>
                        <strong>Submitted:</strong> {$requestData['requested_at']}</p>
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
                        <p><strong>Email:</strong> <a href='mailto:it-support@webuild.com'>it-support@webuild.com</a><br>
                        <strong>Phone:</strong> (123) 456-7890 ext. 2<br>
                        <strong>Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
                    </div>

                    <p>We appreciate your interest in using our WITMS system and look forward to providing you with access soon.</p>
                    
                    <p>Best regards,<br>
                    <strong>WeBuild IT Support Team</strong><br>
                    Information Technology Department</p>
                </div>
                
                <div class='footer'>
                    <p><strong>WeBuild Company</strong> - Building Excellence Together</p>
                    <p>This is an automated confirmation email. Please do not reply directly to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}