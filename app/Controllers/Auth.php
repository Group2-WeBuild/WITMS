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
    }

    public function resetPassword(): string
    {
        return view(name: 'auth/reset_password');
    }

    public function contactAdministrator(): string
    {
        return view(name: 'auth/contact_admin');
    }
}