<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\DepartmentModel;
use App\Models\RoleModel;

class Dashboard extends BaseController
{
    protected $userModel;
    protected $departmentModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->departmentModel = new DepartmentModel();
        $this->roleModel = new RoleModel();
    }

    /**
     * Default dashboard - redirects based on user role
     */
    public function index()
    {
        $userRole = session()->get('user_role');
        
        switch ($userRole) {
            case 'Warehouse Manager':
                return redirect()->to('/dashboard/warehouse-manager');
            case 'Warehouse Staff':
                return redirect()->to('/dashboard/warehouse-staff');
            case 'Inventory Auditor':
                return redirect()->to('/dashboard/inventory-auditor');
            case 'Procurement Officer':
                return redirect()->to('/dashboard/procurement-officer');
            case 'Accounts Payable Clerk':
                return redirect()->to('/dashboard/accounts-payable');
            case 'Accounts Receivable Clerk':
                return redirect()->to('/dashboard/accounts-receivable');
            case 'IT Administrator':
                return redirect()->to('/dashboard/it-administrator');
            case 'Top Management':
                return redirect()->to('/dashboard/top-management');
            default:
                return redirect()->to('/auth/login');
        }
    }    /**
     * Warehouse Manager Dashboard
     */
    public function warehouseManager()
    {
        $this->checkRoleAccess(['Warehouse Manager']);
        
        $data = [
            'title' => 'Warehouse Manager Dashboard - WeBuild WITMS',
            'user' => $this->getUserData(),
            'stats' => $this->getWarehouseStats(),
            'department_stats' => $this->getDepartmentStats(),
            'team_members' => $this->getUsersByCurrentRole()
        ];
        
        return view('users/warehouse_manager/dashboard', $data);
    }    /**
     * Warehouse Staff Dashboard
     */
    public function warehouseStaff()
    {
        $this->checkRoleAccess(['Warehouse Staff']);
        
        $data = [
            'title' => 'Warehouse Staff Dashboard - WeBuild WITMS',
            'user' => $this->getUserData(),
            'department_stats' => $this->getDepartmentStats()
        ];
        
        return view('users/warehouse_staff/dashboard', $data);
    }    /**
     * Inventory Auditor Dashboard
     */
    public function inventoryAuditor()
    {
        $this->checkRoleAccess(['Inventory Auditor']);
        
        $data = [
            'title' => 'Inventory Auditor Dashboard - WeBuild WITMS',
            'user' => $this->getUserData(),
            'department_stats' => $this->getDepartmentStats(),
            'warehouse_stats' => $this->getWarehouseStats()
        ];
        
        return view('users/inventory_auditor/dashboard', $data);
    }    /**
     * Procurement Officer Dashboard
     */
    public function procurementOfficer()
    {
        $this->checkRoleAccess(['Procurement Officer']);
        
        $data = [
            'title' => 'Procurement Officer Dashboard - WeBuild WITMS',
            'user' => $this->getUserData(),
            'department_stats' => $this->getDepartmentStats()
        ];
        
        return view('users/procurement_officer/dashboard', $data);
    }    /**
     * Accounts Payable Clerk Dashboard
     */
    public function accountsPayable()
    {
        $this->checkRoleAccess(['Accounts Payable Clerk']);
        
        $data = [
            'title' => 'Accounts Payable Dashboard - WeBuild WITMS',
            'user' => $this->getUserData(),
            'department_stats' => $this->getDepartmentStats()
        ];
        
        return view('users/accounts_payable/dashboard', $data);
    }    /**
     * Accounts Receivable Clerk Dashboard
     */
    public function accountsReceivable()
    {
        $this->checkRoleAccess(['Accounts Receivable Clerk']);
        
        $data = [
            'title' => 'Accounts Receivable Dashboard - WeBuild WITMS',
            'user' => $this->getUserData(),
            'department_stats' => $this->getDepartmentStats()
        ];
        
        return view('users/accounts_receivable/dashboard', $data);
    }/**
     * IT Administrator Dashboard
     */
    public function itAdministrator()
    {
        $this->checkRoleAccess(['IT Administrator']);
        
        $data = [
            'title' => 'IT Administrator Dashboard - WeBuild WITMS',
            'user' => $this->getUserData(),
            'user_stats' => $this->userModel->getUserStats(),
            'it_stats' => $this->getITAdminStats(),
            'departments' => $this->departmentModel->getActiveDepartments(),
            'roles' => $this->roleModel->getActiveRoles()
        ];
        
        return view('users/it_administrator/dashboard', $data);
    }    /**
     * Top Management Dashboard
     */
    public function topManagement()
    {
        $this->checkRoleAccess(['Top Management']);
        
        $data = [
            'title' => 'Executive Dashboard - WeBuild WITMS',
            'user' => $this->getUserData(),
            'company_stats' => $this->getCompanyStats()
        ];
        
        return view('users/top_management/dashboard', $data);
    }

    /**
     * Check if user has access to specific roles
     */
    private function checkRoleAccess(array $allowedRoles)
    {
        $userRole = session()->get('user_role');
        
        if (!in_array($userRole, $allowedRoles)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied for your role.');
        }
    }    /**
     * Get current user data from session
     */
    private function getUserData(): array
    {
        return [
            'id' => session()->get('user_id'),
            'email' => session()->get('user_email'),
            'role' => session()->get('user_role'),
            'role_id' => session()->get('role_id'),
            'role_description' => session()->get('role_description'),
            'full_name' => session()->get('full_name'),
            'first_name' => session()->get('first_name'),
            'middle_name' => session()->get('middle_name'),
            'last_name' => session()->get('last_name'),
            'department_id' => session()->get('department_id'),
            'department_name' => session()->get('department_name'),
            'department_location' => session()->get('department_location'),
            'permissions' => session()->get('permissions'),
            'login_time' => session()->get('login_time')
        ];
    }
    
    /**
     * Get warehouse statistics with proper role-based queries
     */
    private function getWarehouseStats(): array
    {
        // Get different role counts using RoleModel
        $warehouseManagerRole = $this->roleModel->getRoleByName('Warehouse Manager');
        $warehouseStaffRole = $this->roleModel->getRoleByName('Warehouse Staff');
        $inventoryAuditorRole = $this->roleModel->getRoleByName('Inventory Auditor');
        $procurementOfficerRole = $this->roleModel->getRoleByName('Procurement Officer');
        
        // Count active staff by role using UserModel
        $managerCount = 0;
        $staffCount = 0;
        $auditorCount = 0;
        $procurementCount = 0;
        
        if ($warehouseManagerRole) {
            $managers = $this->userModel->getUsersByRole($warehouseManagerRole['id']);
            $managerCount = is_array($managers) ? count($managers) : 0;
        }
        
        if ($warehouseStaffRole) {
            $staff = $this->userModel->getUsersByRole($warehouseStaffRole['id']);
            $staffCount = is_array($staff) ? count($staff) : 0;
        }
        
        if ($inventoryAuditorRole) {
            $auditors = $this->userModel->getUsersByRole($inventoryAuditorRole['id']);
            $auditorCount = is_array($auditors) ? count($auditors) : 0;
        }
        
        if ($procurementOfficerRole) {
            $procurement = $this->userModel->getUsersByRole($procurementOfficerRole['id']);
            $procurementCount = is_array($procurement) ? count($procurement) : 0;
        }
        
        // Get warehouse departments using DepartmentModel
        $warehouseDepartments = $this->departmentModel->getWarehouseDepartments();
        $departmentCount = is_array($warehouseDepartments) ? count($warehouseDepartments) : 0;
        
        // Calculate total warehouse personnel
        $totalWarehouseStaff = $managerCount + $staffCount + $auditorCount + $procurementCount;
        
        return [
            'total_items' => 0, // Placeholder for future inventory module
            'low_stock_items' => 0, // Placeholder for future inventory module
            'pending_orders' => 0, // Placeholder for future orders module
            'active_staff' => $staffCount,
            'total_warehouse_personnel' => $totalWarehouseStaff,
            'warehouse_managers' => $managerCount,
            'warehouse_staff' => $staffCount,
            'inventory_auditors' => $auditorCount,
            'procurement_officers' => $procurementCount,
            'warehouse_departments' => $departmentCount,
            'departments_list' => $warehouseDepartments
        ];
    }

    /**
     * Get company-wide statistics for executives using all models
     */
    private function getCompanyStats(): array
    {
        // Get user statistics from UserModel
        $userStats = $this->userModel->getUserStats();
        
        // Get all departments from DepartmentModel
        $allDepartments = $this->departmentModel->findAll();
        $activeDepartments = $this->departmentModel->getActiveDepartments();
        
        // Get department statistics
        $departmentStats = $this->departmentModel->getDepartmentStats();
        
        // Get all roles from RoleModel
        $allRoles = $this->roleModel->findAll();
        $activeRoles = $this->roleModel->getActiveRoles();
        
        // Get departments with user counts
        $departmentsWithCounts = $this->departmentModel->getDepartmentsWithUserCount();
        
        // Calculate role distribution percentages
        $roleDistribution = [];
        if (isset($userStats['total_users']) && $userStats['total_users'] > 0) {
            foreach ($allRoles as $role) {
                $roleKey = str_replace(' ', '_', strtolower($role['name']));
                $count = $userStats['role_counts'][$roleKey] ?? 0;
                $percentage = ($count / $userStats['total_users']) * 100;
                
                $roleDistribution[$role['name']] = [
                    'count' => $count,
                    'percentage' => round($percentage, 2),
                    'role_id' => $role['id']
                ];
            }
        }
        
        // Get location distribution
        $locationStats = [];
        foreach ($allDepartments as $dept) {
            $location = $dept['warehouse_location'];
            if (!isset($locationStats[$location])) {
                $locationStats[$location] = [
                    'count' => 0,
                    'departments' => []
                ];
            }
            $locationStats[$location]['count']++;
            $locationStats[$location]['departments'][] = $dept['name'];
        }
        
        return [
            // User Statistics
            'user_stats' => $userStats,
            'total_users' => $userStats['total_users'] ?? 0,
            'active_users' => $userStats['active_users'] ?? 0,
            'inactive_users' => $userStats['inactive_users'] ?? 0,
            'role_distribution' => $roleDistribution,
            
            // Department Statistics
            'departments' => $allDepartments,
            'active_departments' => $activeDepartments,
            'total_departments' => count($allDepartments),
            'active_departments_count' => count($activeDepartments),
            'department_stats' => $departmentStats,
            'departments_with_user_counts' => $departmentsWithCounts,
            'location_distribution' => $locationStats,
            
            // Role Statistics
            'roles' => $allRoles,
            'active_roles' => $activeRoles,
            'total_roles' => count($allRoles),
            
            // System Health
            'system_health' => 'Good',
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Get IT administrator statistics
     */
    private function getITAdminStats(): array
    {
        // Get all user statistics
        $userStats = $this->userModel->getUserStats();
        
        // Get all users with their role details
        $allUsersWithRoles = $this->userModel->getAllUsersWithRoles();
        
        // Get departments needing managers
        $departmentsNeedingManagers = $this->departmentModel->getDepartmentsNeedingManagers();
        
        // Get IT administrators
        $itAdmins = $this->userModel->getITAdministrators();
        
        // Get all active roles
        $activeRoles = $this->roleModel->getActiveRoles();
        
        // Recent user activity (placeholder)
        $recentLogins = []; // This would come from a login log table in the future
        
        return [
            'user_stats' => $userStats,
            'all_users' => $allUsersWithRoles,
            'total_users' => count($allUsersWithRoles),
            'departments_needing_managers' => $departmentsNeedingManagers,
            'it_administrators' => $itAdmins,
            'it_admin_count' => count($itAdmins),
            'active_roles' => $activeRoles,
            'recent_logins' => $recentLogins,
            'system_health' => 'Good'
        ];
    }
    
    /**
     * Get department-specific statistics
     */
    private function getDepartmentStats(?int $departmentId = null): array
    {
        $departmentId = $departmentId ?? session()->get('department_id');
        
        if (!$departmentId) {
            return [
                'department' => null,
                'users' => [],
                'user_count' => 0
            ];
        }
        
        // Get department details
        $department = $this->departmentModel->find($departmentId);
        
        // Get users in this department with their roles
        $departmentUsers = $this->userModel->getUsersByDepartment($departmentId);
        
        // Get role distribution within department
        $roleDistribution = [];
        foreach ($departmentUsers as $user) {
            $userWithRole = $this->userModel->getUserWithRole($user['id']);
            $roleName = $userWithRole['role_name'] ?? 'Unknown';
            
            if (!isset($roleDistribution[$roleName])) {
                $roleDistribution[$roleName] = 0;
            }
            $roleDistribution[$roleName]++;
        }
        
        return [
            'department' => $department,
            'users' => $departmentUsers,
            'user_count' => count($departmentUsers),
            'role_distribution' => $roleDistribution,
            'location' => $department['warehouse_location'] ?? 'Unknown'
        ];
    }
    
    /**
     * Get role-specific user list
     */
    private function getUsersByCurrentRole(): array
    {
        $currentRoleName = session()->get('user_role');
        
        if (!$currentRoleName) {
            return [];
        }
        
        // Get users with the same role
        $users = $this->userModel->getUsersByRoleName($currentRoleName);
        
        return $users;
    }
}
