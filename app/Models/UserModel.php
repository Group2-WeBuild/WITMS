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
    protected $protectFields = true;
    
    protected $allowedFields = [
        'email',
        'password',
        'role',
        'department',
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'is_active',
        'last_login',
        'email_verified_at'
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
        ],
        'password' => [
            'label' => 'Password',
            'rules' => 'required|min_length[8]'
        ],
        'role' => [
            'label' => 'Role',
            'rules' => 'required|in_list[Warehouse Manager,Warehouse Staff,Inventory Auditor,Procurement Officer,Accounts Payable Clerk,Accounts Receivable Clerk,IT Administrator,System Administrator]'
        ],
        'first_name' => [
            'label' => 'First Name',
            'rules' => 'required|max_length[100]'
        ],
        'last_name' => [
            'label' => 'Last Name',
            'rules' => 'required|max_length[100]'
        ],
        'middle_name' => [
            'label' => 'Middle Name',
            'rules' => 'permit_empty|max_length[100]'
        ],
        'department' => [
            'label' => 'Department',
            'rules' => 'permit_empty|max_length[100]'
        ],
        'phone_number' => [
            'label' => 'Phone Number',
            'rules' => 'permit_empty|max_length[20]'
        ],
        'is_active' => [
            'label' => 'Active Status',
            'rules' => 'permit_empty|in_list[0,1]'
        ]
    ];
    
    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email address is already registered.'
        ],
        'password' => [
            'min_length' => 'Password must be at least 8 characters long.'
        ],
        'role' => [
            'in_list' => 'Please select a valid role.'
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
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash(password: $data['data']['password'], algo: PASSWORD_DEFAULT);
        }
        
        return $data;
    }
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): array|object|null
    {
        return $this->where(key: 'email', value: $email)->first();
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify(password: $password, hash: $hash);
    }
    
    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
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
    public function getUsersByRole(string $role)
    {
        return $this->where('role', $role)->where('is_active', 1)->findAll();
    }
    
    /**
     * Get users by department
     */
    public function getUsersByDepartment(string $department)
    {
        return $this->where('department', $department)->where('is_active', 1)->findAll();
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
        
        // Get role distribution
        $roles = [
            'Warehouse Manager',
            'Warehouse Staff',
            'Inventory Auditor',
            'Procurement Officer',
            'Accounts Payable Clerk',
            'Accounts Receivable Clerk',
            'IT Administrator',
            'System Administrator'
        ];
        
        foreach ($roles as $role) {
            $stats['role_counts'][str_replace(' ', '_', strtolower($role))] = 
                $this->where('role', $role)->where('is_active', 1)->countAllResults(false);
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
        $userData['role'] = $userData['role'] ?? 'Warehouse Staff';
        
        return $this->insert($userData);
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
        $user = $this->find($userId);
        return !empty($user['email_verified_at']);
    }
}