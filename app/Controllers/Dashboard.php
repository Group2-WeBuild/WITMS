<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\DepartmentModel;
use App\Models\RoleModel;
use App\Models\InventoryModel;
use App\Models\MaterialModel;
use App\Models\MaterialCategoryModel;
use App\Models\UserWarehouseAssignmentModel;

class Dashboard extends BaseController
{
    protected $userModel;
    protected $departmentModel;
    protected $roleModel;
    protected $inventoryModel;
    protected $materialModel;
    protected $categoryModel;
    protected $userWarehouseAssignmentModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->departmentModel = new DepartmentModel();
        $this->roleModel = new RoleModel();
        $this->inventoryModel = new InventoryModel();
        $this->materialModel = new MaterialModel();
        $this->categoryModel = new MaterialCategoryModel();
        $this->userWarehouseAssignmentModel = new UserWarehouseAssignmentModel();
    }

    /**
     * Default dashboard - redirects based on user role
     */
    public function index()
    {
        $userRole = session()->get('user_role');
        
        switch ($userRole) {
            case 'Warehouse Manager':
                return redirect()->to('/warehouse-manager/dashboard');
            case 'Warehouse Staff':
                return redirect()->to('/warehouse-staff/dashboard');
            case 'Inventory Auditor':
                return redirect()->to('/inventory-auditor/dashboard');
            case 'Procurement Officer':
                return redirect()->to('/procurement-officer/dashboard');
            case 'Accounts Payable Clerk':
                return redirect()->to('/accounts-payable/dashboard');
            case 'Accounts Receivable Clerk':
                return redirect()->to('/accounts-receivable/dashboard');
            case 'IT Administrator':
                return redirect()->to('/it-administrator/dashboard');
            case 'Top Management':
                return redirect()->to('/top-management/dashboard');
            default:
                return redirect()->to('/auth/login');
        }
    }    
    
    /**
     * Warehouse Manager Dashboard
     */
    public function warehouseManager()
    {
        $this->checkRoleAccess(['Warehouse Manager']);
        
        // Get assigned warehouses for the current user
        $userId = session()->get('user_id');
        $assignedWarehouses = [];
        $assignedWarehouseIds = null;
        
        if ($userId) {
            $assignedWarehouses = $this->userWarehouseAssignmentModel->getWarehousesByUser($userId, true);
            if (!empty($assignedWarehouses)) {
                $assignedWarehouseIds = array_column($assignedWarehouses, 'warehouse_id');
            }
        }
        
        $data = [
            'title' => 'Warehouse Manager Dashboard - WeBuild WITMS',
            'user' => $this->getUserData(),
            'stats' => $this->getWarehouseStats($assignedWarehouseIds),
            'department_stats' => $this->getDepartmentStats(),
            'team_members' => $this->getUsersByCurrentRole(),
            'assigned_warehouses' => $assignedWarehouses
        ];
        
        return view('users/warehouse_manager/dashboard', $data);
    }    
    
    /**
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
    }    
    
    /**
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
    }    
    
    /**
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
    }    
    
    /**
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
    }    
    
    /**
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
    }
    
    /**
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
    }    
    
    /**
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
    }    
    
    /**
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
     * @param array|null $warehouseIds Optional array of warehouse IDs to filter by
     */
    private function getWarehouseStats(?array $warehouseIds = null): array
    {
        // Get different role counts using RoleModel
        $warehouseManagerRole = $this->roleModel->getRoleByName('Warehouse Manager');
        $warehouseStaffRole = $this->roleModel->getRoleByName('Warehouse Staff');
        $inventoryAuditorRole = $this->roleModel->getRoleByName('Inventory Auditor');
        $procurementOfficerRole = $this->roleModel->getRoleByName('Procurement Officer');
        
        // Count active staff by role - filter by assigned warehouses if provided
        $managerCount = 0;
        $staffCount = 0;
        $auditorCount = 0;
        $procurementCount = 0;
        
        if ($warehouseIds !== null && !empty($warehouseIds)) {
            // Filter staff by assigned warehouses
            if ($warehouseManagerRole) {
                $allManagers = $this->userModel->getUsersByRole($warehouseManagerRole['id']);
                $managers = $this->filterUsersByWarehouses($allManagers, $warehouseIds);
                $managerCount = is_array($managers) ? count($managers) : 0;
            }
            
            if ($warehouseStaffRole) {
                $allStaff = $this->userModel->getUsersByRole($warehouseStaffRole['id']);
                $staff = $this->filterUsersByWarehouses($allStaff, $warehouseIds);
                $staffCount = is_array($staff) ? count($staff) : 0;
            }
            
            if ($inventoryAuditorRole) {
                $allAuditors = $this->userModel->getUsersByRole($inventoryAuditorRole['id']);
                $auditors = $this->filterUsersByWarehouses($allAuditors, $warehouseIds);
                $auditorCount = is_array($auditors) ? count($auditors) : 0;
            }
            
            if ($procurementOfficerRole) {
                $allProcurement = $this->userModel->getUsersByRole($procurementOfficerRole['id']);
                $procurement = $this->filterUsersByWarehouses($allProcurement, $warehouseIds);
                $procurementCount = is_array($procurement) ? count($procurement) : 0;
            }
        } else {
            // No filtering - count all staff (for IT Admin or when no warehouse IDs provided)
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
        }
        
        // Get warehouse departments using DepartmentModel
        $warehouseDepartments = $this->departmentModel->getWarehouseDepartments();
        $departmentCount = is_array($warehouseDepartments) ? count($warehouseDepartments) : 0;
        
        // Calculate total warehouse personnel
        $totalWarehouseStaff = $managerCount + $staffCount + $auditorCount + $procurementCount;
        
        // Get real-time inventory and materials statistics filtered by assigned warehouses
        $inventoryStats = $this->getInventoryStatsForWarehouses($warehouseIds);
        $materialStats = $this->materialModel->getMaterialStats();
        
        return [
            'total_items' => $inventoryStats['total_items'] ?? 0,
            'low_stock_items' => $inventoryStats['low_stock_items'] ?? 0,
            'expiring_soon' => $inventoryStats['expiring_soon'] ?? 0,
            'total_quantity' => $inventoryStats['total_quantity'] ?? 0,
            'total_value' => $inventoryStats['total_value'] ?? 0,
            'total_materials' => $materialStats['total_materials'] ?? 0,
            'active_materials' => $materialStats['active_materials'] ?? 0,
            'perishable_materials' => $materialStats['perishable_materials'] ?? 0,
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
     * Get inventory statistics filtered by warehouse IDs
     * @param array|null $warehouseIds Array of warehouse IDs to filter by, or null for all
     */
    private function getInventoryStatsForWarehouses(?array $warehouseIds): array
    {
        $builder = $this->inventoryModel->db->table('inventory');

        // Filter by warehouse IDs if provided
        if ($warehouseIds !== null && !empty($warehouseIds)) {
            $builder->whereIn('warehouse_id', $warehouseIds);
        }

        // Count total items
        $totalItems = $builder->countAllResults(false);
        
        // Get total quantity
        $builderQty = $this->inventoryModel->db->table('inventory');
        if ($warehouseIds !== null && !empty($warehouseIds)) {
            $builderQty->whereIn('warehouse_id', $warehouseIds);
        }
        $totalQty = $builderQty->selectSum('quantity')->get()->getRow()->quantity ?? 0;

        // Get total value
        $totalValue = $this->getTotalInventoryValueForWarehouses($warehouseIds);

        // Get low stock items
        $lowStockItems = $this->getLowStockItemsForWarehouses($warehouseIds);
        
        // Get expiring items
        $expiringItems = $this->getExpiringItemsForWarehouses($warehouseIds);

        return [
            'total_items' => $totalItems,
            'total_quantity' => $totalQty,
            'total_value' => $totalValue,
            'low_stock' => count($lowStockItems),
            'low_stock_items' => count($lowStockItems),
            'expiring' => count($expiringItems),
            'expiring_soon' => count($expiringItems),
            'expiring_items' => count($expiringItems)
        ];
    }

    /**
     * Get total inventory value for specific warehouses
     */
    private function getTotalInventoryValueForWarehouses(?array $warehouseIds): float
    {
        $builder = $this->inventoryModel->db->table('inventory')
            ->select('SUM(inventory.available_quantity * materials.unit_cost) as total_value')
            ->join('materials', 'materials.id = inventory.material_id')
            ->where('inventory.available_quantity >', 0);

        if ($warehouseIds !== null && !empty($warehouseIds)) {
            $builder->whereIn('inventory.warehouse_id', $warehouseIds);
        }

        $result = $builder->get()->getRow();
        return (float)($result->total_value ?? 0);
    }

    /**
     * Get low stock items for specific warehouses
     */
    private function getLowStockItemsForWarehouses(?array $warehouseIds): array
    {
        $builder = $this->inventoryModel->db->table('inventory')
            ->select('inventory.*, materials.reorder_level')
            ->join('materials', 'materials.id = inventory.material_id')
            ->where('inventory.available_quantity <=', 'materials.reorder_level', false)
            ->where('materials.reorder_level >', 0);

        if ($warehouseIds !== null && !empty($warehouseIds)) {
            $builder->whereIn('inventory.warehouse_id', $warehouseIds);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get expiring items for specific warehouses
     */
    private function getExpiringItemsForWarehouses(?array $warehouseIds, int $days = 30): array
    {
        $expiryDate = date('Y-m-d', strtotime("+{$days} days"));
        
        $builder = $this->inventoryModel->db->table('inventory')
            ->where('expiration_date IS NOT NULL')
            ->where('expiration_date <=', $expiryDate)
            ->where('expiration_date >=', date('Y-m-d'));

        if ($warehouseIds !== null && !empty($warehouseIds)) {
            $builder->whereIn('warehouse_id', $warehouseIds);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Filter users by warehouse assignments
     * Returns only users who are assigned to at least one of the specified warehouses
     */
    private function filterUsersByWarehouses(array $users, array $warehouseIds): array
    {
        $filteredUsers = [];
        
        foreach ($users as $user) {
            // Get user's warehouse assignments
            $userAssignments = $this->userWarehouseAssignmentModel->getWarehousesByUser($user['id'], true);
            $userWarehouseIds = array_column($userAssignments, 'warehouse_id');
            
            // Include if user is assigned to any of the specified warehouses
            if (!empty(array_intersect($userWarehouseIds, $warehouseIds))) {
                $filteredUsers[] = $user;
            }
        }
        
        return $filteredUsers;
    }

    /**
     * Get company-wide statistics for executives using all models
     */
    private function getCompanyStats(): array
    {        // Get user statistics from UserModel
        $userStats = $this->userModel->getUserStats();
        
        // Get all departments with warehouse details from DepartmentModel
        $allDepartmentsWithWarehouse = $this->departmentModel->getAllDepartmentsWithWarehouse();
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
        
        // Get location distribution by warehouse (using warehouse_id instead of warehouse_location)
        $locationStats = [
            'Central Office' => [
                'count' => 0,
                'departments' => []
            ]
        ];
        
        foreach ($allDepartmentsWithWarehouse as $dept) {
            // If warehouse_id is null, it's a Central Office department
            if (empty($dept['warehouse_id'])) {
                $locationStats['Central Office']['count']++;
                $locationStats['Central Office']['departments'][] = $dept['name'];
            } else {
                // Use warehouse name from joined data
                $location = $dept['warehouse_name'] ?? 'Unknown Warehouse';
                if (!isset($locationStats[$location])) {
                    $locationStats[$location] = [
                        'count' => 0,
                        'departments' => []
                    ];
                }
                $locationStats[$location]['count']++;
                $locationStats[$location]['departments'][] = $dept['name'];
            }
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
