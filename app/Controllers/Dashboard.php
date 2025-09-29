<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\DepartmentModel;

class Dashboard extends BaseController
{
    protected $userModel;
    protected $departmentModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->departmentModel = new DepartmentModel();
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
    }

    /**
     * Warehouse Manager Dashboard
     */
    public function warehouseManager()
    {
        $this->checkRoleAccess(['Warehouse Manager']);
        
        $data = [
            'title' => 'Warehouse Manager Dashboard - WeBuild WITMS',
            'user' => $this->getUserData(),
            'stats' => $this->getWarehouseStats()
        ];
        
        return view('dashboard/warehouse_manager', $data);
    }

    /**
     * Warehouse Staff Dashboard
     */
    public function warehouseStaff()
    {
        $this->checkRoleAccess(['Warehouse Staff']);
        
        $data = [
            'title' => 'Warehouse Staff Dashboard - WeBuild WITMS',
            'user' => $this->getUserData()
        ];
        
        return view('dashboard/warehouse_staff', $data);
    }

    /**
     * Inventory Auditor Dashboard
     */
    public function inventoryAuditor()
    {
        $this->checkRoleAccess(['Inventory Auditor']);
        
        $data = [
            'title' => 'Inventory Auditor Dashboard - WeBuild WITMS',
            'user' => $this->getUserData()
        ];
        
        return view('dashboard/inventory_auditor', $data);
    }

    /**
     * Procurement Officer Dashboard
     */
    public function procurementOfficer()
    {
        $this->checkRoleAccess(['Procurement Officer']);
        
        $data = [
            'title' => 'Procurement Officer Dashboard - WeBuild WITMS',
            'user' => $this->getUserData()
        ];
        
        return view('dashboard/procurement_officer', $data);
    }

    /**
     * Accounts Payable Clerk Dashboard
     */
    public function accountsPayable()
    {
        $this->checkRoleAccess(['Accounts Payable Clerk']);
        
        $data = [
            'title' => 'Accounts Payable Dashboard - WeBuild WITMS',
            'user' => $this->getUserData()
        ];
        
        return view('dashboard/accounts_payable', $data);
    }

    /**
     * Accounts Receivable Clerk Dashboard
     */
    public function accountsReceivable()
    {
        $this->checkRoleAccess(['Accounts Receivable Clerk']);
        
        $data = [
            'title' => 'Accounts Receivable Dashboard - WeBuild WITMS',
            'user' => $this->getUserData()
        ];
        
        return view('dashboard/accounts_receivable', $data);
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
            'user_stats' => $this->userModel->getUserStats()
        ];
        
        return view('dashboard/it_administrator', $data);
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
        
        return view('dashboard/top_management', $data);
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
            'full_name' => session()->get('full_name'),
            'first_name' => session()->get('first_name'),
            'department_name' => session()->get('department_name'),
            'department_location' => session()->get('department_location'),
            'permissions' => session()->get('permissions'),
            'login_time' => session()->get('login_time')
        ];
    }

    /**
     * Get warehouse statistics
     */
    private function getWarehouseStats(): array
    {
        // This would be replaced with actual warehouse statistics
        return [
            'total_items' => 0,
            'low_stock_items' => 0,
            'pending_orders' => 0,
            'active_staff' => $this->userModel->getUsersByRole('Warehouse Staff')
        ];
    }

    /**
     * Get company-wide statistics for executives
     */
    private function getCompanyStats(): array
    {
        return [
            'total_users' => $this->userModel->getUserStats(),
            'departments' => $this->departmentModel->findAll(),
            'system_health' => 'Good'
        ];
    }
}
