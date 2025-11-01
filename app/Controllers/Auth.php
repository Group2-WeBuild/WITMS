<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\DepartmentModel;
use App\Models\RoleModel;

class Auth extends BaseController
{
    protected $userModel;
    protected $departmentModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->departmentModel = new DepartmentModel();
        $this->roleModel = new RoleModel();
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
            }            // Get department information
            $department = null;
            if (!empty($user['department_id'])) {
                $department = $this->departmentModel->find($user['department_id']);
            }

            // Get role information
            $role = null;
            if (!empty($user['role_id'])) {
                $role = $this->roleModel->find($user['role_id']);
            }

            // Prepare session data based on user role
            $sessionData = $this->prepareSessionData($user, $department, $role);

            // Set session data
            session()->set($sessionData);

            // Update last login timestamp
            $this->userModel->updateLastLogin($user['id']);

            // Set remember me cookie if requested
            if ($remember) {
                $this->setRememberMeCookie($user['id']);
            }

            // Log successful login
            log_message('info', "User {$user['email']} ({$role['name']}) logged in successfully");

            // Redirect to appropriate dashboard based on role
            return $this->redirectToDashboard($role['name']);

        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'An error occurred during login. Please try again.');
        }
    }    /**
     * Prepare comprehensive session data based on user role
     */
    private function prepareSessionData(array $user, ?array $department, ?array $role): array
    {
        $fullName = $this->userModel->getFullName($user);
        $roleName = $role['name'] ?? 'Unknown';
        
        // Base session data for all users
        $sessionData = [
            'isLoggedIn' => true,
            'user_id' => $user['id'],
            'user_email' => $user['email'],
            'user_role' => $roleName,
            'role_id' => $user['role_id'],
            'role_description' => $role['description'] ?? null,
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
        switch ($roleName) {
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
     */    private function sendPasswordResetEmail(array $user, string $token, ?string $employeeId)
    {
        // Initialize email service (uses config from Email.php)
        $email = \Config\Services::email();

        // Email content
        $resetLink = base_url("auth/reset-password-confirm/{$token}");
        $fullName = $this->userModel->getFullName($user);
        
        $subject = 'Password Reset Request - WeBuild WITMS';
        
        $message = $this->buildPasswordResetEmailTemplate($fullName, $resetLink, $user['role_id'], $employeeId);

        // Set email parameters
        $email->setTo($user['email']);
        $email->setSubject($subject);
        $email->setMessage($message);

        // Send email
        if ($email->send()) {
            log_message('info', "Password reset email sent to: {$user['email']}");
            return true;
        } else {
            $error = $email->printDebugger(['headers', 'subject', 'body']);
            log_message('error', "Failed to send password reset email to: {$user['email']}. Error: {$error}");
            // For development, log the reset link
            log_message('info', "Password reset link for {$user['email']}: {$resetLink}");
            return false;
        }
    }    /**
     * Build password reset email template
     */
    private function buildPasswordResetEmailTemplate(string $fullName, string $resetLink, string $role, ?string $employeeId): string
    {
        $data = [
            'fullName' => $fullName,
            'resetLink' => $resetLink,
            'role' => $role,
            'employeeId' => $employeeId
        ];

        return view('emails/password_reset', $data);
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
        }        // Verify token and show password reset form
        $user = $this->userModel->findByResetToken($token);
        
        if (!$user || $this->userModel->isResetTokenExpired($user['id'])) {
            return redirect()->to('/auth/login')
                           ->with('error', 'Password reset link has expired or is invalid. Please request a new one.');
        }

        // Get user with role details for display
        $userWithRole = $this->userModel->getUserWithRole($user['id']);
        
        $data = [
            'title' => 'Set New Password - WeBuild WITMS',
            'token' => $token,
            'user' => $userWithRole ?? $user,
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
    }    
    /**
     * Display contact administrator page or handle contact form submission
     */
    public function contactAdministrator()
    {
        // Handle contact form submission
        if ($this->request->getMethod() === 'POST') {
            return $this->handleContactAdminRequest();
        }

        // Get active departments for the dropdown
        $departments = $this->departmentModel->getActiveDepartments();
        
        // Get active roles for the dropdown
        $roles = $this->roleModel->getActiveRoles();

        // Display contact admin form
        $data = [
            'title' => 'Contact Administrator - WeBuild WITMS',
            'validation' => \Config\Services::validation(),
            'departments' => $departments,
            'roles' => $roles
        ];

        return view('auth/contact_admin', $data);
    }    /**
     * Handle contact admin form submission
     */
    private function handleContactAdminRequest()
    {
        // Get all active role names for validation
        $roleNames = $this->roleModel->getAllRoleNames();        // Validation rules
        // Regex pattern: accepts letters (including ñÑ), spaces, dots, and "Jr."
        // Pattern explanation: ^[a-zA-ZñÑ]+(\s[a-zA-ZñÑ]+)*(\sJr\.?)?$
        // - Starts with letters (including ñÑ)
        // - Can have additional words separated by spaces
        // - Optionally ends with " Jr." or " Jr"
        $namePattern = '/^[a-zA-ZñÑ]+(\s[a-zA-ZñÑ]+)*(\sJr\.?)?$/';
        
        $rules = [
            'first_name' => [
                'label' => 'First Name',
                'rules' => 'required|max_length[100]|regex_match[' . $namePattern . ']',
                'errors' => [
                    'regex_match' => 'First Name can only contain letters, spaces, "Jr.", and Spanish characters (ñ, Ñ).'
                ]
            ],
            'middle_name' => [
                'label' => 'Middle Name',
                'rules' => 'permit_empty|max_length[100]|regex_match[' . $namePattern . ']',
                'errors' => [
                    'regex_match' => 'Middle Name can only contain letters, spaces, "Jr.", and Spanish characters (ñ, Ñ).'
                ]
            ],            'last_name' => [
                'label' => 'Last Name', 
                'rules' => 'required|max_length[100]|regex_match[' . $namePattern . ']',
                'errors' => [
                    'regex_match' => 'Last Name can only contain letters, spaces, "Jr.", and Spanish characters (ñ, Ñ).'
                ]
            ],
            'email' => [
                'label' => 'Email Address',
                'rules' => 'required|valid_email|max_length[255]|regex_match[/^[a-zA-Z0-9._]+@gmail\.com$/]',
                'errors' => [
                    'valid_email' => 'Please enter a valid email address.',
                    'regex_match' => 'Only Gmail addresses (@gmail.com) are accepted. Special characters like quotes or symbols (except . _ % + -) are not allowed.'
                ]
            ],
            'phone' => [
                'label' => 'Phone Number',
                'rules' => 'permit_empty|max_length[20]|regex_match[/^(\+639|09)\d{9}$/]',
                'errors' => [
                    'regex_match' => 'Phone number must be in the format +639XXXXXXXXX or 09XXXXXXXXX (11 digits).'
                ]
            ],
            'department' => [
                'label' => 'Department',
                'rules' => 'required|integer|greater_than[0]|is_not_unique[departments.id]'
            ],
            'role' => [
                'label' => 'Requested Role',
                'rules' => 'required|in_list[' . implode(',', $roleNames) . ']'
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
        }        try {
            // Get form data
            $requestData = [
                'first_name' => $this->request->getPost('first_name'),
                'middle_name' => $this->request->getPost('middle_name'),
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

            // Build full name for logging
            $fullName = trim($requestData['first_name'] . ' ' . ($requestData['middle_name'] ?? '') . ' ' . $requestData['last_name']);

            // Log the request
            log_message('info', "Account access request submitted by: {$requestData['email']} ({$fullName})");

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
            // Get IT administrators using UserModel
            $itAdministrators = $this->userModel->getITAdministrators();
            
            if (empty($itAdministrators)) {
                log_message('warning', 'No IT administrators found to send contact admin notification');
                return false;
            }

            $email = \Config\Services::email();
            
            // Email subject
            $subject = 'New Account Access Request - WeBuild WITMS';
            
            // Email content
            $message = $this->buildContactAdminEmailTemplate($requestData);            $emailsSent = 0;
            foreach ($itAdministrators as $admin) {
                $email->clear();
                $email->setTo($admin['email']);
                $email->setSubject($subject);
                $email->setMessage($message);

                if ($email->send()) {
                    $emailsSent++;
                    log_message('info', "Contact admin notification sent to: {$admin['email']}");
                } else {
                    $error = $email->printDebugger(['headers']);
                    log_message('error', "Failed to send contact admin notification to: {$admin['email']}. Error: {$error}");
                }
            }

            // Send confirmation email to requester
            $this->sendContactAdminConfirmation($requestData);

            return $emailsSent > 0;

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
            
            $email->setTo($requestData['email']);
            $email->setSubject('Account Access Request Received - WeBuild WITMS');
            $email->setMessage($this->buildContactAdminConfirmationTemplate($requestData));

            if ($email->send()) {
                log_message('info', "Confirmation email sent to: {$requestData['email']}");
                return true;
            } else {
                $error = $email->printDebugger(['headers']);
                log_message('error', "Failed to send confirmation email to: {$requestData['email']}. Error: {$error}");
                return false;
            }

        } catch (\Exception $e) {
            log_message('error', "Contact admin confirmation error: " . $e->getMessage());
            return false;
        }
    }    /**
     * Build email template for IT administrator notification
     */
    private function buildContactAdminEmailTemplate(array $requestData): string
    {
        // Get department name from ID using DepartmentModel
        $departmentName = 'Unknown Department';
        $departmentLocation = '';
        
        if (!empty($requestData['department'])) {
            $department = $this->departmentModel->find($requestData['department']);
            if ($department) {
                $departmentName = $department['name'];
                if (!empty($department['warehouse_location']) && $department['warehouse_location'] !== 'All Warehouses') {
                    $departmentLocation = $department['warehouse_location'];
                    $departmentName .= ' (' . $departmentLocation . ')';
                }
            }
        }

        // Get role details from RoleModel
        $roleName = $requestData['role'] ?? 'Unknown Role';
        $roleDescription = '';
        
        $role = $this->roleModel->getRoleByName($roleName);
        if ($role && !empty($role['description'])) {
            $roleDescription = $role['description'];
        }

        // Build full name
        $fullName = trim($requestData['first_name'] . ' ' . ($requestData['middle_name'] ?? '') . ' ' . $requestData['last_name']);

        $data = [
            'requestData' => $requestData,
            'departmentName' => $departmentName,
            'roleName' => $roleName,
            'roleDescription' => $roleDescription,
            'fullName' => $fullName
        ];

        return view('emails/contact_admin_notification', $data);
    }    /**
     * Build confirmation email template for the requester
     */
    private function buildContactAdminConfirmationTemplate(array $requestData): string
    {
        // Get department name from ID using DepartmentModel
        $departmentName = 'Unknown Department';
        
        if (!empty($requestData['department'])) {
            $department = $this->departmentModel->find($requestData['department']);
            if ($department) {
                $departmentName = $department['name'];
            }
        }

        // Build full name
        $fullName = trim($requestData['first_name'] . ' ' . ($requestData['middle_name'] ?? '') . ' ' . $requestData['last_name']);

        $data = [
            'requestData' => $requestData,
            'departmentName' => $departmentName,
            'roleName' => $requestData['role'],
            'fullName' => $fullName
        ];

        return view('emails/contact_admin_confirmation', $data);
    }
}