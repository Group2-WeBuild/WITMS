<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\DepartmentModel;
use App\Models\WarehouseModel;
use App\Models\WarehouseLocationModel;
use App\Models\UserWarehouseAssignmentModel;

class ITAdministratorController extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $departmentModel;
    protected $warehouseModel;
    protected $warehouseLocationModel;
    protected $userWarehouseAssignmentModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->departmentModel = new DepartmentModel();
        $this->warehouseModel = new WarehouseModel();
        $this->warehouseLocationModel = new WarehouseLocationModel();
        $this->userWarehouseAssignmentModel = new UserWarehouseAssignmentModel();
    }

    /**
     * Check if user has access (IT Administrator role)
     */
    protected function checkAccess()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login to continue');
        }

        $userRole = session()->get('user_role');
        $allowedRoles = ['IT Administrator'];
        
        if (!in_array($userRole, $allowedRoles)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        return null;
    }

    /**
     * Get user data for views
     */
    protected function getUserData(): array
    {
        return [
            'id' => session()->get('user_id'),
            'email' => session()->get('user_email'),
            'role' => session()->get('user_role'),
            'role_id' => session()->get('role_id'),
            'full_name' => session()->get('full_name'),
            'first_name' => session()->get('first_name'),
            'last_name' => session()->get('last_name'),
        ];
    }

    /**
     * User Management - List all users
     */
    public function users()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $users = $this->userModel->getAllUsersWithRoles();
        $roles = $this->roleModel->getActiveRoles();
        $departments = $this->departmentModel->getActiveDepartments();
        $stats = $this->userModel->getUserStats();
        $currentUserId = session()->get('user_id');

        $data = [
            'title' => 'User Management - WITMS',
            'user' => $this->getUserData(),
            'users' => $users,
            'roles' => $roles,
            'departments' => $departments,
            'stats' => $stats,
            'current_user_id' => $currentUserId
        ];

        return view('users/it_administrator/user_management', $data);
    }

    /**
     * Get user details for editing (AJAX)
     */
    public function getUser()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $userId = $this->request->getPost('user_id');
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User ID is required']);
        }

        $user = $this->userModel->getUserWithDetails($userId);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        return $this->response->setJSON(['success' => true, 'user' => $user]);
    }

    /**
     * Create new user (AJAX)
     */
    public function createUser()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'middle_name' => $this->request->getPost('middle_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone_number' => $this->request->getPost('phone_number'),
            'role_id' => $this->request->getPost('role_id'),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'password' => $this->request->getPost('password'),
            'is_active' => $this->request->getPost('is_active') == '1' ? 1 : 0
        ];

        // Validate required fields
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['role_id']) || empty($data['password'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Please fill in all required fields']);
        }

        try {
            // Use insert directly to get the insert ID
            if ($this->userModel->insert($data)) {
                $userId = $this->userModel->getInsertID();
                return $this->response->setJSON(['success' => true, 'message' => 'User created successfully', 'user_id' => $userId]);
            } else {
                $errors = $this->userModel->errors();
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to create user', 'errors' => $errors]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating user: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Update user (AJAX)
     */
    public function updateUser()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $userId = $this->request->getPost('user_id');
        $currentUserId = session()->get('user_id');

        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User ID is required']);
        }

        // Check if trying to edit self
        if ($userId == $currentUserId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You cannot edit your own account']);
        }

        // Get user to check role
        $user = $this->userModel->getUserWithRole($userId);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        // Check if trying to edit IT Administrator
        if ($user['role_name'] == 'IT Administrator') {
            // Allow editing other IT Admins, but log it
            log_message('info', 'IT Administrator ' . $currentUserId . ' is editing IT Administrator ' . $userId);
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'middle_name' => $this->request->getPost('middle_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone_number' => $this->request->getPost('phone_number'),
            'role_id' => $this->request->getPost('role_id'),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'is_active' => $this->request->getPost('is_active') == '1' ? 1 : 0
        ];

        // Only update password if provided
        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password');
        }

        // Remove empty values
        $data = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });

        try {
            // Password is optional on update, so skip validation if not provided
            if (!isset($data['password'])) {
                $this->userModel->skipValidation(true);
            }

            if ($this->userModel->update($userId, $data)) {
                $this->userModel->skipValidation(false); // Reset validation
                return $this->response->setJSON(['success' => true, 'message' => 'User updated successfully']);
            } else {
                $errors = $this->userModel->errors();
                $this->userModel->skipValidation(false); // Reset validation
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to update user', 'errors' => $errors]);
            }
        } catch (\Exception $e) {
            $this->userModel->skipValidation(false); // Reset validation
            log_message('error', 'Error updating user: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Soft delete user (set is_active = 0) (AJAX)
     */
    public function softDeleteUser()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $userId = $this->request->getPost('user_id');
        $currentUserId = session()->get('user_id');

        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User ID is required']);
        }

        // Check if trying to delete self
        if ($userId == $currentUserId) {
            return $this->response->setJSON(['success' => false, 'message' => 'You cannot delete your own account']);
        }

        // Get user to check role
        $user = $this->userModel->getUserWithRole($userId);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        // Check if trying to delete IT Administrator
        if ($user['role_name'] == 'IT Administrator') {
            return $this->response->setJSON(['success' => false, 'message' => 'IT Administrator accounts cannot be deleted']);
        }

        try {
            if ($this->userModel->update($userId, ['is_active' => 0])) {
                log_message('info', 'User ' . $userId . ' soft deleted by IT Administrator ' . $currentUserId);
                return $this->response->setJSON(['success' => true, 'message' => 'User deactivated successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to deactivate user']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error soft deleting user: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Reactivate user (set is_active = 1) (AJAX)
     */
    public function reactivateUser()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $userId = $this->request->getPost('user_id');
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User ID is required']);
        }

        try {
            if ($this->userModel->update($userId, ['is_active' => 1])) {
                return $this->response->setJSON(['success' => true, 'message' => 'User reactivated successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to reactivate user']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error reactivating user: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Warehouse Assignments - Manage user-warehouse assignments
     */
    public function warehouseAssignments()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $users = $this->userModel->getAllUsersWithRoles();
        $warehouses = $this->warehouseModel->getActiveWarehouses();
        $roles = $this->roleModel->getActiveRoles();

        // Get all assignments with details
        $assignments = [];
        foreach ($users as $user) {
            $userAssignments = $this->userWarehouseAssignmentModel->getWarehousesByUser($user['id'], false);
            if (!empty($userAssignments)) {
                $assignments[$user['id']] = $userAssignments;
            }
        }

        $data = [
            'title' => 'Warehouse Assignments - WITMS',
            'user' => $this->getUserData(),
            'users' => $users,
            'warehouses' => $warehouses,
            'roles' => $roles,
            'assignments' => $assignments
        ];

        return view('users/it_administrator/warehouse_assignments', $data);
    }

    /**
     * Assign user to warehouse (AJAX)
     */
    public function assignUserToWarehouse()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $userId = $this->request->getPost('user_id');
        $warehouseId = $this->request->getPost('warehouse_id');
        $isPrimary = $this->request->getPost('is_primary') == '1';
        $roleId = $this->request->getPost('role_id');
        $notes = $this->request->getPost('notes');

        if (!$userId || !$warehouseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User ID and Warehouse ID are required']);
        }

        try {
            $data = [];
            if ($isPrimary) {
                $data['is_primary'] = true;
            }
            if ($roleId) {
                $data['role_id'] = $roleId;
            }
            if ($notes) {
                $data['notes'] = $notes;
            }

            $result = $this->userWarehouseAssignmentModel->assignUserToWarehouse($userId, $warehouseId, $data);

            if ($result) {
                if ($isPrimary) {
                    $this->userWarehouseAssignmentModel->setPrimaryWarehouse($userId, $warehouseId);
                }
                return $this->response->setJSON(['success' => true, 'message' => 'User assigned to warehouse successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to assign user to warehouse']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error assigning user to warehouse: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove user from warehouse (AJAX)
     */
    public function removeUserFromWarehouse()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $userId = $this->request->getPost('user_id');
        $warehouseId = $this->request->getPost('warehouse_id');

        if (!$userId || !$warehouseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User ID and Warehouse ID are required']);
        }

        try {
            $result = $this->userWarehouseAssignmentModel->removeUserFromWarehouse($userId, $warehouseId);

            if ($result) {
                return $this->response->setJSON(['success' => true, 'message' => 'User removed from warehouse successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to remove user from warehouse']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error removing user from warehouse: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Set primary warehouse for user (AJAX)
     */
    public function setPrimaryWarehouse()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $userId = $this->request->getPost('user_id');
        $warehouseId = $this->request->getPost('warehouse_id');

        if (!$userId || !$warehouseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User ID and Warehouse ID are required']);
        }

        try {
            $result = $this->userWarehouseAssignmentModel->setPrimaryWarehouse($userId, $warehouseId);

            if ($result) {
                return $this->response->setJSON(['success' => true, 'message' => 'Primary warehouse set successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to set primary warehouse']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error setting primary warehouse: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get user's warehouse assignments (AJAX)
     */
    public function getUserWarehouses()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $userId = $this->request->getPost('user_id');

        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'User ID is required']);
        }

        try {
            $warehouses = $this->userWarehouseAssignmentModel->getWarehousesByUser($userId, false);
            return $this->response->setJSON(['success' => true, 'warehouses' => $warehouses]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching user warehouses: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }


    /**
     * Department Management
     */
    public function departments()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // Get all departments (including inactive for management)
        $departments = $this->departmentModel->select('departments.*, warehouses.name as warehouse_name, warehouses.code as warehouse_code')
                                            ->join('warehouses', 'warehouses.id = departments.warehouse_id', 'left')
                                            ->orderBy('departments.is_active', 'DESC')
                                            ->orderBy('departments.name', 'ASC')
                                            ->findAll();
        
        $warehouses = $this->warehouseModel->getActiveWarehouses();
        $stats = $this->departmentModel->getDepartmentStats();

        $data = [
            'title' => 'Department Management - WITMS',
            'user' => $this->getUserData(),
            'departments' => $departments,
            'warehouses' => $warehouses,
            'stats' => $stats
        ];

        return view('users/it_administrator/department_management', $data);
    }

    /**
     * Get department details (AJAX)
     */
    public function getDepartment()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $departmentId = $this->request->getPost('department_id');
        if (!$departmentId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Department ID is required']);
        }

        $department = $this->departmentModel->getDepartmentWithWarehouse($departmentId);
        if (!$department) {
            return $this->response->setJSON(['success' => false, 'message' => 'Department not found']);
        }

        return $this->response->setJSON(['success' => true, 'department' => $department]);
    }

    /**
     * Create department (AJAX)
     */
    public function createDepartment()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?: null,
            'warehouse_id' => $this->request->getPost('warehouse_id') ?: null,
            'department_head' => $this->request->getPost('department_head') ?: null,
            'contact_email' => $this->request->getPost('contact_email') ?: null,
            'contact_phone' => $this->request->getPost('contact_phone') ?: null,
            'is_active' => $this->request->getPost('is_active') == '1' ? 1 : 0
        ];

        // Validate required fields
        if (empty($data['name'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Department name is required']);
        }

        try {
            if ($this->departmentModel->insert($data)) {
                log_message('info', 'Department created by IT Administrator: ' . $data['name']);
                return $this->response->setJSON(['success' => true, 'message' => 'Department created successfully']);
            } else {
                $errors = $this->departmentModel->errors();
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to create department', 'errors' => $errors]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating department: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Update department (AJAX)
     */
    public function updateDepartment()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $departmentId = $this->request->getPost('department_id');
        if (!$departmentId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Department ID is required']);
        }

        $department = $this->departmentModel->find($departmentId);
        if (!$department) {
            return $this->response->setJSON(['success' => false, 'message' => 'Department not found']);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?: null,
            'warehouse_id' => $this->request->getPost('warehouse_id') ?: null,
            'department_head' => $this->request->getPost('department_head') ?: null,
            'contact_email' => $this->request->getPost('contact_email') ?: null,
            'contact_phone' => $this->request->getPost('contact_phone') ?: null,
            'is_active' => $this->request->getPost('is_active') == '1' ? 1 : 0
        ];

        try {
            if ($this->departmentModel->update($departmentId, $data)) {
                log_message('info', 'Department updated by IT Administrator: ' . $departmentId);
                return $this->response->setJSON(['success' => true, 'message' => 'Department updated successfully']);
            } else {
                $errors = $this->departmentModel->errors();
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to update department', 'errors' => $errors]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating department: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Soft delete department (set is_active = 0) (AJAX)
     */
    public function softDeleteDepartment()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $departmentId = $this->request->getPost('department_id');
        if (!$departmentId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Department ID is required']);
        }

        try {
            // Check if department has users
            $userModel = new \App\Models\UserModel();
            $hasUsers = $userModel->where('department_id', $departmentId)->countAllResults() > 0;

            if ($hasUsers) {
                // Soft delete (deactivate) if has users
                if ($this->departmentModel->update($departmentId, ['is_active' => 0])) {
                    log_message('info', 'Department deactivated by IT Administrator: ' . $departmentId);
                    return $this->response->setJSON(['success' => true, 'message' => 'Department deactivated successfully (has users assigned)']);
                }
            } else {
                // Can be fully deactivated if no users
                if ($this->departmentModel->update($departmentId, ['is_active' => 0])) {
                    log_message('info', 'Department deactivated by IT Administrator: ' . $departmentId);
                    return $this->response->setJSON(['success' => true, 'message' => 'Department deactivated successfully']);
                }
            }

            return $this->response->setJSON(['success' => false, 'message' => 'Failed to deactivate department']);
        } catch (\Exception $e) {
            log_message('error', 'Error soft deleting department: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Reactivate department (set is_active = 1) (AJAX)
     */
    public function reactivateDepartment()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $departmentId = $this->request->getPost('department_id');
        if (!$departmentId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Department ID is required']);
        }

        try {
            if ($this->departmentModel->update($departmentId, ['is_active' => 1])) {
                return $this->response->setJSON(['success' => true, 'message' => 'Department reactivated successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to reactivate department']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error reactivating department: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * System Settings
     */
    public function settings()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'title' => 'System Settings - WITMS',
            'user' => $this->getUserData()
        ];

        return view('users/it_administrator/system_settings', $data);
    }

    /**
     * Database Backup
     */
    public function backup()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // Get backup directory
        $backupDir = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Get list of existing backups
        $backups = [];
        if (is_dir($backupDir)) {
            $files = scandir($backupDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                    $filePath = $backupDir . $file;
                    $backups[] = [
                        'filename' => $file,
                        'size' => filesize($filePath),
                        'created' => date('Y-m-d H:i:s', filemtime($filePath)),
                        'size_formatted' => $this->formatBytes(filesize($filePath))
                    ];
                }
            }
            // Sort by creation date (newest first)
            usort($backups, function($a, $b) {
                return strtotime($b['created']) - strtotime($a['created']);
            });
        }

        // Get database info
        $db = \Config\Database::connect();
        $dbConfig = config('Database');
        $defaultGroup = $dbConfig->defaultGroup;
        $defaultConfig = $dbConfig->default;

        $data = [
            'title' => 'Database Backup - WITMS',
            'user' => $this->getUserData(),
            'backups' => $backups,
            'database_name' => $defaultConfig['database'],
            'backup_dir' => $backupDir
        ];

        return view('users/it_administrator/database_backup', $data);
    }

    /**
     * Create database backup (AJAX)
     */
    public function createBackup()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $db = \Config\Database::connect();
            $dbConfig = config('Database');
            $defaultConfig = $dbConfig->default;

            $hostname = $defaultConfig['hostname'];
            $username = $defaultConfig['username'];
            $password = $defaultConfig['password'];
            $database = $defaultConfig['database'];
            $port = $defaultConfig['port'] ?? 3306;

            // Create backup directory if it doesn't exist
            $backupDir = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
            if (!is_dir($backupDir)) {
                if (!mkdir($backupDir, 0755, true)) {
                    throw new \Exception('Failed to create backup directory');
                }
            }

            // Generate backup filename
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "witms_db_backup_{$timestamp}.sql";
            $filepath = $backupDir . $filename;

            // Build mysqldump command
            // Works for both MySQL and MariaDB
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --quick --lock-tables=false %s > %s 2>&1',
                escapeshellarg($hostname),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            // Execute backup command
            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($filepath) || filesize($filepath) == 0) {
                $error = implode("\n", $output);
                log_message('error', 'Backup failed: ' . $error);
                
                // Try alternative method using PHP if mysqldump fails
                $this->createBackupPHP($filepath, $db, $database);
            }

            if (!file_exists($filepath) || filesize($filepath) == 0) {
                throw new \Exception('Backup file was not created or is empty');
            }

            $fileSize = filesize($filepath);
            log_message('info', "Database backup created: {$filename} ({$fileSize} bytes)");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Backup created successfully',
                'filename' => $filename,
                'size' => $this->formatBytes($fileSize),
                'created' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error creating backup: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error creating backup: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Alternative backup method using PHP (fallback if mysqldump is not available)
     */
    private function createBackupPHP($filepath, $db, $database)
    {
        $output = "-- WITMS Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Database: {$database}\n\n";
        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "SET AUTOCOMMIT = 0;\n";
        $output .= "START TRANSACTION;\n";
        $output .= "SET time_zone = \"+00:00\";\n\n";

        // Get all tables
        $tables = $db->listTables();

        foreach ($tables as $table) {
            $output .= "\n-- Table structure for table `{$table}`\n";
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            
            // Get table structure
            $createTable = $db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
            if ($createTable) {
                $output .= $createTable['Create Table'] . ";\n\n";
            }

            // Get table data
            $rows = $db->query("SELECT * FROM `{$table}`")->getResultArray();
            if (!empty($rows)) {
                $output .= "-- Dumping data for table `{$table}`\n";
                $output .= "INSERT INTO `{$table}` VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            // Escape the value properly for SQL
                            $escaped = addslashes($value);
                            $rowValues[] = "'" . str_replace(["\n", "\r"], ["\\n", "\\r"], $escaped) . "'";
                        }
                    }
                    $values[] = "(" . implode(",", $rowValues) . ")";
                }
                $output .= implode(",\n", $values) . ";\n\n";
            }
        }

        $output .= "COMMIT;\n";

        file_put_contents($filepath, $output);
    }

    /**
     * Download backup file
     */
    public function downloadBackup($filename)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $backupDir = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
        $filepath = $backupDir . basename($filename);

        // Security check: ensure file is in backup directory
        if (!file_exists($filepath) || strpos(realpath($filepath), realpath($backupDir)) !== 0) {
            return redirect()->to('/it-administrator/backup')->with('error', 'Backup file not found');
        }

        return $this->response->download($filepath, null);
    }

    /**
     * Delete backup file (AJAX)
     */
    public function deleteBackup()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $filename = $this->request->getPost('filename');
        if (!$filename) {
            return $this->response->setJSON(['success' => false, 'message' => 'Filename is required']);
        }

        $backupDir = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
        $filepath = $backupDir . basename($filename);

        // Security check
        if (!file_exists($filepath) || strpos(realpath($filepath), realpath($backupDir)) !== 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Backup file not found']);
        }

        if (unlink($filepath)) {
            log_message('info', "Backup file deleted: {$filename}");
            return $this->response->setJSON(['success' => true, 'message' => 'Backup deleted successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete backup']);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * IT Administrator Dashboard
     */
    public function dashboard()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // Get statistics from models
        $userStats = $this->userModel->getUserStats();
        $warehouseStats = $this->warehouseModel->getWarehouseStats();
        $departmentStats = $this->departmentModel->getDepartmentStats();

        // Get recent system alerts from logs
        $systemAlerts = $this->getRecentSystemAlerts(10);
        $alertCount = count($systemAlerts);

        // Get backup status
        $backupStatus = $this->getBackupStatus();

        // Get warehouse assignments statistics
        $assignmentStats = $this->getWarehouseAssignmentStats();

        // Get active sessions (users logged in today)
        $activeSessions = $this->getActiveSessions();

        $data = [
            'title' => 'IT Administrator Dashboard - WITMS',
            'user' => $this->getUserData(),
            'userStats' => $userStats,
            'warehouseStats' => $warehouseStats,
            'departmentStats' => $departmentStats,
            'systemAlerts' => $systemAlerts,
            'alertCount' => $alertCount,
            'backupStatus' => $backupStatus,
            'assignmentStats' => $assignmentStats,
            'activeSessions' => $activeSessions
        ];

        return view('users/it_administrator/dashboard', $data);
    }

    /**
     * Get recent system alerts from log files
     */
    private function getRecentSystemAlerts($limit = 10)
    {
        $logDir = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR;
        $todayLog = 'log-' . date('Y-m-d') . '.log';
        $yesterdayLog = 'log-' . date('Y-m-d', strtotime('-1 day')) . '.log';
        
        $alerts = [];
        $filesToCheck = [];
        
        if (file_exists($logDir . $todayLog)) {
            $filesToCheck[] = $logDir . $todayLog;
        }
        if (file_exists($logDir . $yesterdayLog)) {
            $filesToCheck[] = $logDir . $yesterdayLog;
        }

        foreach ($filesToCheck as $filepath) {
            $stats = [];
            $entries = $this->parseLogFile($filepath, $stats, null, null);
            
            // Filter for critical, error, and warning entries only
            foreach ($entries as $entry) {
                if (in_array($entry['level'], ['critical', 'error', 'warning', 'alert', 'emergency'])) {
                    $alerts[] = $entry;
                }
            }
        }

        // Sort by timestamp (newest first) and limit
        usort($alerts, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($alerts, 0, $limit);
    }

    /**
     * Get backup status
     */
    private function getBackupStatus()
    {
        $backupDir = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR;
        
        if (!is_dir($backupDir)) {
            return [
                'status' => 'warning',
                'message' => 'Backup directory not found',
                'last_backup' => null,
                'backup_count' => 0
            ];
        }

        $backups = [];
        if (is_dir($backupDir)) {
            $files = scandir($backupDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && $file != 'index.html' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
                    $filepath = $backupDir . $file;
                    $backups[] = [
                        'filename' => $file,
                        'size' => filesize($filepath),
                        'modified' => filemtime($filepath)
                    ];
                }
            }
        }

        if (empty($backups)) {
            return [
                'status' => 'warning',
                'message' => 'No backups found',
                'last_backup' => null,
                'backup_count' => 0
            ];
        }

        // Sort by modification time (newest first)
        usort($backups, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        $lastBackup = $backups[0];
        $daysSinceBackup = floor((time() - $lastBackup['modified']) / (60 * 60 * 24));

        $status = 'success';
        $message = 'Last backup: ' . date('M j, Y H:i', $lastBackup['modified']);
        
        if ($daysSinceBackup > 7) {
            $status = 'warning';
            $message .= ' (' . $daysSinceBackup . ' days ago)';
        } elseif ($daysSinceBackup > 14) {
            $status = 'danger';
            $message .= ' (' . $daysSinceBackup . ' days ago - Backup overdue!)';
        }

        return [
            'status' => $status,
            'message' => $message,
            'last_backup' => date('Y-m-d H:i:s', $lastBackup['modified']),
            'backup_count' => count($backups),
            'days_since_backup' => $daysSinceBackup
        ];
    }

    /**
     * Get warehouse assignment statistics
     */
    private function getWarehouseAssignmentStats()
    {
        $totalAssignments = $this->userWarehouseAssignmentModel->where('is_active', 1)->countAllResults();
        $usersWithAssignments = $this->userWarehouseAssignmentModel->select('user_id')
            ->where('is_active', 1)
            ->distinct()
            ->countAllResults();
        $usersWithoutAssignments = $this->userModel->countAll() - $usersWithAssignments;
        $primaryWarehouses = $this->userWarehouseAssignmentModel->where('is_primary', 1)
            ->where('is_active', 1)
            ->countAllResults();

        return [
            'total_assignments' => $totalAssignments,
            'users_with_assignments' => $usersWithAssignments,
            'users_without_assignments' => $usersWithoutAssignments,
            'primary_warehouses' => $primaryWarehouses
        ];
    }

    /**
     * Get active sessions (users who logged in today)
     */
    private function getActiveSessions()
    {
        $today = date('Y-m-d');
        return $this->userModel->where('DATE(last_login)', $today)
            ->where('is_active', 1)
            ->countAllResults();
    }

    /**
     * System Logs
     */
    public function logs()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $logDir = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR;
        
        // Get all log files
        $logFiles = [];
        if (is_dir($logDir)) {
            $files = scandir($logDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && $file != 'index.html' && pathinfo($file, PATHINFO_EXTENSION) == 'log') {
                    $filePath = $logDir . $file;
                    $logFiles[] = [
                        'filename' => $file,
                        'date' => $this->extractDateFromLogFilename($file),
                        'size' => filesize($filePath),
                        'size_formatted' => $this->formatBytes(filesize($filePath)),
                        'modified' => date('Y-m-d H:i:s', filemtime($filePath))
                    ];
                }
            }
            // Sort by date (newest first)
            usort($logFiles, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
        }

        // Get today's log file for default view
        $todayLog = 'log-' . date('Y-m-d') . '.log';
        $selectedFile = $this->request->getGet('file') ?: $todayLog;

        // Get log entries from selected file
        $logEntries = [];
        $stats = [
            'total' => 0,
            'emergency' => 0,
            'alert' => 0,
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'notice' => 0,
            'info' => 0,
            'debug' => 0
        ];

        if (file_exists($logDir . $selectedFile)) {
            $logEntries = $this->parseLogFile($logDir . $selectedFile, $stats);
        }

        $data = [
            'title' => 'System Logs - WITMS',
            'user' => $this->getUserData(),
            'logFiles' => $logFiles,
            'selectedFile' => $selectedFile,
            'logEntries' => $logEntries,
            'stats' => $stats,
            'logDir' => $logDir
        ];

        return view('users/it_administrator/system_logs', $data);
    }

    /**
     * Get log entries (AJAX) - for filtering and searching
     */
    public function getLogEntries()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $filename = $this->request->getPost('filename');
        $level = $this->request->getPost('level'); // Filter by level
        $search = $this->request->getPost('search'); // Search term

        if (!$filename) {
            return $this->response->setJSON(['success' => false, 'message' => 'Filename is required']);
        }

        $logDir = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR;
        $filepath = $logDir . basename($filename);

        // Security check
        if (!file_exists($filepath) || strpos(realpath($filepath), realpath($logDir)) !== 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Log file not found']);
        }

        $stats = [
            'total' => 0,
            'emergency' => 0,
            'alert' => 0,
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'notice' => 0,
            'info' => 0,
            'debug' => 0
        ];

        $logEntries = $this->parseLogFile($filepath, $stats, $level, $search);

        return $this->response->setJSON([
            'success' => true,
            'entries' => $logEntries,
            'stats' => $stats
        ]);
    }

    /**
     * Download log file
     */
    public function downloadLog($filename)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $logDir = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR;
        $filepath = $logDir . basename($filename);

        // Security check
        if (!file_exists($filepath) || strpos(realpath($filepath), realpath($logDir)) !== 0) {
            return redirect()->to('/it-administrator/logs')->with('error', 'Log file not found');
        }

        return $this->response->download($filepath, null);
    }

    /**
     * Delete log file (AJAX)
     */
    public function deleteLog()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $filename = $this->request->getPost('filename');
        if (!$filename) {
            return $this->response->setJSON(['success' => false, 'message' => 'Filename is required']);
        }

        $logDir = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR;
        $filepath = $logDir . basename($filename);

        // Security check
        if (!file_exists($filepath) || strpos(realpath($filepath), realpath($logDir)) !== 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Log file not found']);
        }

        // Prevent deleting today's log
        $todayLog = 'log-' . date('Y-m-d') . '.log';
        if ($filename === $todayLog) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cannot delete today\'s log file']);
        }

        if (unlink($filepath)) {
            log_message('info', "Log file deleted by IT Administrator: {$filename}");
            return $this->response->setJSON(['success' => true, 'message' => 'Log file deleted successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete log file']);
        }
    }

    /**
     * Clear old logs (older than specified days) (AJAX)
     */
    public function clearOldLogs()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $days = (int)$this->request->getPost('days') ?: 30;
        $logDir = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR;
        $todayLog = 'log-' . date('Y-m-d') . '.log';
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));

        $deletedCount = 0;
        $errors = [];

        if (is_dir($logDir)) {
            $files = scandir($logDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && $file != 'index.html' && pathinfo($file, PATHINFO_EXTENSION) == 'log') {
                    // Skip today's log
                    if ($file === $todayLog) {
                        continue;
                    }

                    $fileDate = $this->extractDateFromLogFilename($file);
                    if ($fileDate && strtotime($fileDate) < strtotime($cutoffDate)) {
                        $filepath = $logDir . $file;
                        if (unlink($filepath)) {
                            $deletedCount++;
                        } else {
                            $errors[] = $file;
                        }
                    }
                }
            }
        }

        log_message('info', "Cleared {$deletedCount} old log files (older than {$days} days) by IT Administrator");

        return $this->response->setJSON([
            'success' => true,
            'message' => "Deleted {$deletedCount} log file(s)",
            'deleted_count' => $deletedCount,
            'errors' => $errors
        ]);
    }

    /**
     * Parse log file and extract entries
     */
    private function parseLogFile($filepath, &$stats, $filterLevel = null, $searchTerm = null)
    {
        $entries = [];
        $content = file_get_contents($filepath);
        $lines = explode("\n", $content);

        $currentEntry = null;
        $entryLines = [];

        foreach ($lines as $line) {
            // Check if line starts with a log level (new entry)
            if (preg_match('/^(EMERGENCY|ALERT|CRITICAL|ERROR|WARNING|NOTICE|INFO|DEBUG)\s*-\s*(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s*-->\s*(.+)$/', $line, $matches)) {
                // Save previous entry if exists
                if ($currentEntry !== null) {
                    $currentEntry['message'] = implode("\n", $entryLines);
                    $currentEntry['full_text'] = implode("\n", $entryLines);
                    
                    // Apply filters
                    if ($this->shouldIncludeEntry($currentEntry, $filterLevel, $searchTerm)) {
                        $entries[] = $currentEntry;
                        $stats[$currentEntry['level']]++;
                        $stats['total']++;
                    }
                }

                // Start new entry
                $currentEntry = [
                    'level' => strtolower($matches[1]),
                    'timestamp' => $matches[2],
                    'message' => $matches[3],
                    'full_text' => $matches[3]
                ];
                $entryLines = [$matches[3]];
            } else {
                // Continuation of previous entry (stack trace, etc.)
                if ($currentEntry !== null && trim($line) !== '') {
                    $entryLines[] = $line;
                }
            }
        }

        // Don't forget the last entry
        if ($currentEntry !== null) {
            $currentEntry['message'] = implode("\n", $entryLines);
            $currentEntry['full_text'] = implode("\n", $entryLines);
            
            if ($this->shouldIncludeEntry($currentEntry, $filterLevel, $searchTerm)) {
                $entries[] = $currentEntry;
                $stats[$currentEntry['level']]++;
                $stats['total']++;
            }
        }

        return $entries;
    }

    /**
     * Check if entry should be included based on filters
     */
    private function shouldIncludeEntry($entry, $filterLevel, $searchTerm)
    {
        // Filter by level
        if ($filterLevel && $entry['level'] !== strtolower($filterLevel)) {
            return false;
        }

        // Filter by search term
        if ($searchTerm && stripos($entry['full_text'], $searchTerm) === false) {
            return false;
        }

        return true;
    }

    /**
     * Extract date from log filename (log-YYYY-MM-DD.log)
     */
    private function extractDateFromLogFilename($filename)
    {
        if (preg_match('/log-(\d{4}-\d{2}-\d{2})\.log$/', $filename, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Configuration
     */
    public function config()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'title' => 'Configuration - WITMS',
            'user' => $this->getUserData()
        ];

        return view('users/it_administrator/configuration', $data);
    }

    /**
     * Warehouse Management - List all warehouses
     */
    public function warehouseManagement()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $warehouses = $this->warehouseModel->getWarehousesWithManagers();
        $stats = [
            'total' => count($warehouses),
            'active' => count(array_filter($warehouses, fn($w) => $w['is_active'])),
            'inactive' => count(array_filter($warehouses, fn($w) => !$w['is_active'])),
            'with_managers' => count(array_filter($warehouses, fn($w) => !empty($w['manager_id'])))
        ];

        // Get warehouse managers for dropdown
        $warehouseManagerRole = $this->roleModel->getRoleByName('Warehouse Manager');
        $managers = [];
        if ($warehouseManagerRole) {
            $managers = $this->userModel->getUsersByRole($warehouseManagerRole['id']);
        }

        $data = [
            'title' => 'Warehouse Management - WITMS',
            'user' => $this->getUserData(),
            'warehouses' => $warehouses,
            'stats' => $stats,
            'managers' => $managers
        ];

        return view('users/it_administrator/warehouse_management', $data);
    }

    /**
     * Create warehouse (AJAX)
     */
    public function createWarehouse()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            // First, create the warehouse location
            $locationData = [
                'street_address' => $this->request->getPost('street_address') ?: null,
                'barangay' => $this->request->getPost('barangay') ?: null,
                'city' => $this->request->getPost('city'),
                'province' => $this->request->getPost('province') ?: null,
                'region' => $this->request->getPost('region') ?: null,
                'postal_code' => $this->request->getPost('postal_code') ?: null,
                'country' => $this->request->getPost('country') ?: 'Philippines',
                'latitude' => $this->request->getPost('latitude') ?: null,
                'longitude' => $this->request->getPost('longitude') ?: null
            ];

            $locationId = $this->warehouseLocationModel->insert($locationData);

            if (!$locationId) {
                $errors = $this->warehouseLocationModel->errors();
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to create location', 'errors' => $errors]);
            }

            // Then, create the warehouse
            $warehouseData = [
                'name' => $this->request->getPost('name'),
                'code' => $this->request->getPost('code'),
                'warehouse_location_id' => $locationId,
                'capacity' => $this->request->getPost('capacity') ?: null,
                'manager_id' => $this->request->getPost('manager_id') ?: null,
                'is_active' => $this->request->getPost('is_active') == '1' ? 1 : 0
            ];

            if ($this->warehouseModel->insert($warehouseData)) {
                log_message('info', 'Warehouse created by IT Administrator: ' . $warehouseData['name']);
                return $this->response->setJSON(['success' => true, 'message' => 'Warehouse created successfully']);
            } else {
                // Rollback: delete the location
                $this->warehouseLocationModel->delete($locationId);
                $errors = $this->warehouseModel->errors();
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to create warehouse', 'errors' => $errors]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creating warehouse: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Get warehouse details (AJAX)
     */
    public function getWarehouse()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $warehouseId = $this->request->getPost('warehouse_id');
        if (!$warehouseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Warehouse ID is required']);
        }

        $warehouse = $this->warehouseModel->getWarehouseWithLocation($warehouseId);
        if (!$warehouse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Warehouse not found']);
        }

        // Format the response to include location as nested object
        $response = [
            'id' => $warehouse['id'],
            'name' => $warehouse['name'],
            'code' => $warehouse['code'],
            'capacity' => $warehouse['capacity'],
            'manager_id' => $warehouse['manager_id'],
            'is_active' => $warehouse['is_active'],
            'location' => [
                'street_address' => $warehouse['street_address'] ?? null,
                'barangay' => $warehouse['barangay'] ?? null,
                'city' => $warehouse['city'] ?? null,
                'province' => $warehouse['province'] ?? null,
                'region' => $warehouse['region'] ?? null,
                'postal_code' => $warehouse['postal_code'] ?? null,
                'country' => $warehouse['country'] ?? null,
                'latitude' => $warehouse['latitude'] ?? null,
                'longitude' => $warehouse['longitude'] ?? null
            ]
        ];

        return $this->response->setJSON(['success' => true, 'warehouse' => $response]);
    }

    /**
     * Update warehouse (AJAX)
     */
    public function updateWarehouse()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $warehouseId = $this->request->getPost('warehouse_id');
        if (!$warehouseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Warehouse ID is required']);
        }

        try {
            $warehouse = $this->warehouseModel->find($warehouseId);
            if (!$warehouse) {
                return $this->response->setJSON(['success' => false, 'message' => 'Warehouse not found']);
            }

            // Update location
            $locationData = [
                'street_address' => $this->request->getPost('street_address') ?: null,
                'barangay' => $this->request->getPost('barangay') ?: null,
                'city' => $this->request->getPost('city'),
                'province' => $this->request->getPost('province') ?: null,
                'region' => $this->request->getPost('region') ?: null,
                'postal_code' => $this->request->getPost('postal_code') ?: null,
                'country' => $this->request->getPost('country') ?: 'Philippines',
                'latitude' => $this->request->getPost('latitude') ?: null,
                'longitude' => $this->request->getPost('longitude') ?: null
            ];

            if ($warehouse['warehouse_location_id']) {
                $this->warehouseLocationModel->update($warehouse['warehouse_location_id'], $locationData);
            }

            // Update warehouse
            $warehouseData = [
                'name' => $this->request->getPost('name'),
                'code' => $this->request->getPost('code'),
                'capacity' => $this->request->getPost('capacity') ?: null,
                'manager_id' => $this->request->getPost('manager_id') ?: null,
                'is_active' => $this->request->getPost('is_active') == '1' ? 1 : 0
            ];

            if ($this->warehouseModel->update($warehouseId, $warehouseData)) {
                log_message('info', 'Warehouse updated by IT Administrator: ' . $warehouseId);
                return $this->response->setJSON(['success' => true, 'message' => 'Warehouse updated successfully']);
            } else {
                $errors = $this->warehouseModel->errors();
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to update warehouse', 'errors' => $errors]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating warehouse: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete warehouse (soft delete by setting is_active = 0) (AJAX)
     */
    public function deleteWarehouse()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $warehouseId = $this->request->getPost('warehouse_id');
        if (!$warehouseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Warehouse ID is required']);
        }

        try {
            // Check if warehouse has inventory
            $inventoryModel = new \App\Models\InventoryModel();
            $hasInventory = $inventoryModel->where('warehouse_id', $warehouseId)->countAllResults() > 0;

            if ($hasInventory) {
                // Soft delete (deactivate) instead of hard delete
                if ($this->warehouseModel->update($warehouseId, ['is_active' => 0])) {
                    log_message('info', 'Warehouse deactivated by IT Administrator: ' . $warehouseId);
                    return $this->response->setJSON(['success' => true, 'message' => 'Warehouse deactivated successfully (has inventory)']);
                }
            } else {
                // Can be fully deleted if no inventory
                if ($this->warehouseModel->delete($warehouseId)) {
                    log_message('info', 'Warehouse deleted by IT Administrator: ' . $warehouseId);
                    return $this->response->setJSON(['success' => true, 'message' => 'Warehouse deleted successfully']);
                }
            }

            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete warehouse']);
        } catch (\Exception $e) {
            log_message('error', 'Error deleting warehouse: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}

