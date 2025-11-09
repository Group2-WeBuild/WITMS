<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;      protected $allowedFields = [
        'email',
        'password',
        'role_id',
        'department_id',
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'is_active',
        'last_login',
        'email_verified_at',
        'reset_token',
        'reset_token_expires'
    ];
    
    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    // Validation
    protected $validationRules = [
        'email' => [
            'label' => 'Email Address',
            'rules' => 'required|valid_email|is_unique[users.email,id,{id}]'
        ],        'password' => [
            'label' => 'Password',
            'rules' => 'required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/]'
        ],
        'role_id' => [
            'label' => 'Role',
            'rules' => 'required|integer|greater_than[0]|is_not_unique[roles.id]'
        ],
        'first_name' => [
            'label' => 'First Name',
            'rules' => 'required|max_length[100]|alpha_space'
        ],
        'last_name' => [
            'label' => 'Last Name',
            'rules' => 'required|max_length[100]|alpha_space'
        ],
        'middle_name' => [
            'label' => 'Middle Name',
            'rules' => 'permit_empty|max_length[100]|alpha_space'
        ],
        'department_id' => [
            'label' => 'Department',
            'rules' => 'permit_empty|integer|greater_than[0]'
        ],
        'phone_number' => [
            'label' => 'Phone Number',
            'rules' => 'permit_empty|max_length[20]|regex_match[/^[\+]?[0-9\s\-\(\)]+$/]'
        ],
        'is_active' => [
            'label' => 'Active Status',
            'rules' => 'permit_empty|in_list[0,1]'
        ]
    ];
    
    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email address is already registered in WeBuild system.'
        ],        'password' => [
            'min_length' => 'Password must be at least 8 characters long.',
            'regex_match' => 'Password must contain uppercase, lowercase, number, and special character.'
        ],
        'role_id' => [
            'required' => 'Please select a valid role.',
            'is_not_unique' => 'The selected role does not exist.'
        ]
    ];
    
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    
    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];
    
    /**
     * Hash password before insert/update
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        
        return $data;
    }
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): array|null
    {
        return $this->where('email', $email)->first();
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * Get user with role details
     */
    public function getUserWithRole(int $userId)
    {
        return $this->select('users.*, roles.name as role_name, roles.description as role_description')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->where('users.id', $userId)
                    ->first();
    }
    
    /**
     * Get user with role and department details
     */
    public function getUserWithDetails(int $userId)
    {
        return $this->select('users.*, roles.name as role_name, roles.description as role_description, departments.name as department_name')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->join('departments', 'departments.id = users.department_id', 'left')
                    ->where('users.id', $userId)
                    ->first();
    }
    
    /**
     * Get all users with role details
     */
    public function getAllUsersWithRoles()
    {
        return $this->select('users.*, roles.name as role_name')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->findAll();
    }
    
    /**
     * Get active users only
     */
    public function getActiveUsers()
    {
        return $this->where('is_active', 1)->findAll();
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole(int $roleId)
    {
        return $this->where('role_id', $roleId)->where('is_active', 1)->findAll();
    }
    
    /**
     * Get users by role name
     */
    public function getUsersByRoleName(string $roleName)
    {
        return $this->select('users.*')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('roles.name', $roleName)
                    ->where('users.is_active', 1)
                    ->findAll();
    }
    
    /**
     * Get users by department
     */
    public function getUsersByDepartment(int $departmentId)
    {
        return $this->where('department_id', $departmentId)
                    ->where('is_active', 1)
                    ->findAll();
    }
    
    /**
     * Get IT administrators
     */
    public function getITAdministrators()
    {
        return $this->select('users.*')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('roles.name', 'IT Administrator')
                    ->where('users.is_active', 1)
                    ->findAll();
    }
    
    /**
     * Activate/Deactivate user
     */
    public function toggleUserStatus(int $userId, bool $status): bool
    {
        return $this->update($userId, ['is_active' => $status]);
    }
    
    /**
     * Get full name
     */
    public function getFullName(array $user): string
    {
        $fullName = $user['first_name'];
        
        if (!empty($user['middle_name'])) {
            $fullName .= ' ' . $user['middle_name'];
        }
        
        $fullName .= ' ' . $user['last_name'];
        
        return $fullName;
    }
    
    /**
     * Search users by name or email
     */
    public function searchUsers(string $keyword)
    {
        return $this->groupStart()
                    ->like('first_name', $keyword)
                    ->orLike('last_name', $keyword)
                    ->orLike('email', $keyword)
                    ->groupEnd()
                    ->where('is_active', 1)
                    ->findAll();
    }
      /**
     * Get user statistics
     */
    public function getUserStats(): array
    {
        $stats = [
            'total_users' => $this->countAll(),
            'active_users' => $this->where('is_active', 1)->countAllResults(false),
            'inactive_users' => $this->where('is_active', 0)->countAllResults(false),
        ];
        
        // Get role distribution using JOIN
        $roleModel = new \App\Models\RoleModel();
        $roles = $roleModel->findAll();
        
        foreach ($roles as $role) {
            $stats['role_counts'][str_replace(' ', '_', strtolower($role['name']))] = 
                $this->where('role_id', $role['id'])->where('is_active', 1)->countAllResults(false);
        }
        
        return $stats;
    }
      
    /**
     * Create new user
     */
    public function createUser(array $userData): bool
    {
        // Set default values
        $userData['is_active'] = $userData['is_active'] ?? 1;
        
        // Set default role to Warehouse Staff if not provided
        if (!isset($userData['role_id'])) {
            $roleModel = new \App\Models\RoleModel();
            $defaultRole = $roleModel->getRoleByName('Warehouse Staff');
            if ($defaultRole) {
                $userData['role_id'] = $defaultRole['id'];
            }
        }
        
        return $this->insert($userData);
    }
      /**
     * Check if user has warehouse access permissions
     */
    public function hasWarehouseAccess(int $userId): bool
    {
        $warehouseRoles = [
            'Warehouse Manager',
            'Warehouse Staff',
            'Inventory Auditor',
            'Procurement Officer'
        ];
        
        $count = $this->select('users.id')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('users.id', $userId)
                    ->where('users.is_active', 1)
                    ->whereIn('roles.name', $warehouseRoles)
                    ->countAllResults();
        
        return $count > 0;
    }
    
    /**
     * Check if user has financial system access
     */
    public function hasFinancialAccess(int $userId): bool
    {
        $financialRoles = [
            'Accounts Payable Clerk',
            'Accounts Receivable Clerk',
            'Top Management'
        ];
        
        $count = $this->select('users.id')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('users.id', $userId)
                    ->where('users.is_active', 1)
                    ->whereIn('roles.name', $financialRoles)
                    ->countAllResults();
        
        return $count > 0;
    }
    
    /**
     * Check if user has management-level access
     */
    public function hasManagementAccess(int $userId): bool
    {
        $managementRoles = [
            'Warehouse Manager',
            'IT Administrator',
            'Top Management'
        ];
        
        $count = $this->select('users.id')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('users.id', $userId)
                    ->where('users.is_active', 1)
                    ->whereIn('roles.name', $managementRoles)
                    ->countAllResults();
        
        return $count > 0;
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(int $userId, array $profileData): bool
    {
        // Remove password from profile data if empty
        if (isset($profileData['password']) && empty($profileData['password'])) {
            unset($profileData['password']);
        }
        
        return $this->update($userId, $profileData);
    }
    
    /**
     * Change user password
     */
    public function changePassword(int $userId, string $newPassword): bool
    {
        return $this->update($userId, ['password' => $newPassword]);
    }
    
    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(int $userId): bool
    {
        return $this->update($userId, ['email_verified_at' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * Check if email is verified
     */
    public function isEmailVerified(int $userId): bool
    {
        $user = $this->select('email_verified_at')
                     ->where('id', $userId)
                     ->first();
        
        return $user && !empty($user['email_verified_at']);
    }

    /**
     * Set password reset token
     */
    public function setPasswordResetToken(int $userId, string $token, string $expiry): bool
    {
        return $this->update($userId, [
            'reset_token' => $token,
            'reset_token_expires' => $expiry
        ]);
    }

    /**
     * Find user by reset token
     */
    public function findByResetToken(string $token): array|null
    {
        return $this->where('reset_token', $token)
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * Check if reset token is expired
     */
    public function isResetTokenExpired(int $userId): bool
    {
        $user = $this->select('reset_token_expires')
                     ->where('id', $userId)
                     ->first();
        
        if (!$user || empty($user['reset_token_expires'])) {
            return true;
        }
        
        return strtotime($user['reset_token_expires']) < time();
    }

    /**
     * Clear password reset token
     */
    public function clearPasswordResetToken(int $userId): bool
    {
        return $this->update($userId, [
            'reset_token' => null,
            'reset_token_expires' => null
        ]);
    }

    /**
     * Find user by email for password reset (more secure)
     */
    public function findByEmailForReset(string $email): array|null
    {
        return $this->where('email', $email)
                    ->where('is_active', 1)
                    ->first();
    }
}