<?php

namespace App\Models;

use CodeIgniter\Model;

class UserWarehouseAssignmentModel extends Model
{
    protected $table            = 'user_warehouse_assignments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'warehouse_id',
        'role_id',
        'is_primary',
        'assigned_by',
        'assigned_at',
        'is_active',
        'notes'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    
    protected array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'warehouse_id' => 'integer',
        'role_id' => '?integer',
        'is_primary' => 'boolean',
        'assigned_by' => '?integer',
        'is_active' => 'boolean'
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'user_id' => [
            'label' => 'User',
            'rules' => 'required|integer|is_not_unique[users.id]'
        ],
        'warehouse_id' => [
            'label' => 'Warehouse',
            'rules' => 'required|integer|is_not_unique[warehouses.id]'
        ],
        'role_id' => [
            'label' => 'Role',
            'rules' => 'permit_empty|integer|is_not_unique[roles.id]'
        ],
        'is_primary' => [
            'label' => 'Is Primary',
            'rules' => 'permit_empty|in_list[0,1]'
        ],
        'is_active' => [
            'label' => 'Is Active',
            'rules' => 'permit_empty|in_list[0,1]'
        ]
    ];

    protected $validationMessages = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get all warehouses assigned to a user
     */
    public function getWarehousesByUser($userId, $activeOnly = true)
    {
        $builder = $this->select('
            user_warehouse_assignments.*,
            warehouses.name as warehouse_name,
            warehouses.code as warehouse_code,
            warehouses.is_active as warehouse_is_active,
            roles.name as role_name
        ')
        ->join('warehouses', 'warehouses.id = user_warehouse_assignments.warehouse_id')
        ->join('roles', 'roles.id = user_warehouse_assignments.role_id', 'left')
        ->where('user_warehouse_assignments.user_id', $userId);

        if ($activeOnly) {
            $builder->where('user_warehouse_assignments.is_active', true)
                    ->where('warehouses.is_active', true);
        }

        return $builder->orderBy('user_warehouse_assignments.is_primary', 'DESC')
                      ->orderBy('warehouses.name', 'ASC')
                      ->findAll();
    }

    /**
     * Get all users assigned to a warehouse
     */
    public function getUsersByWarehouse($warehouseId, $activeOnly = true)
    {
        $builder = $this->select('
            user_warehouse_assignments.*,
            users.first_name,
            users.last_name,
            users.email,
            users.is_active as user_is_active,
            roles.name as role_name
        ')
        ->join('users', 'users.id = user_warehouse_assignments.user_id')
        ->join('roles', 'roles.id = user_warehouse_assignments.role_id', 'left')
        ->where('user_warehouse_assignments.warehouse_id', $warehouseId);

        if ($activeOnly) {
            $builder->where('user_warehouse_assignments.is_active', true)
                    ->where('users.is_active', true);
        }

        return $builder->orderBy('user_warehouse_assignments.is_primary', 'DESC')
                      ->orderBy('users.last_name', 'ASC')
                      ->findAll();
    }

    /**
     * Check if user has access to warehouse
     */
    public function hasAccess($userId, $warehouseId)
    {
        return $this->where('user_id', $userId)
                   ->where('warehouse_id', $warehouseId)
                   ->where('is_active', true)
                   ->first() !== null;
    }

    /**
     * Get primary warehouse for user
     */
    public function getPrimaryWarehouse($userId)
    {
        return $this->select('
            user_warehouse_assignments.*,
            warehouses.name as warehouse_name,
            warehouses.code as warehouse_code
        ')
        ->join('warehouses', 'warehouses.id = user_warehouse_assignments.warehouse_id')
        ->where('user_warehouse_assignments.user_id', $userId)
        ->where('user_warehouse_assignments.is_primary', true)
        ->where('user_warehouse_assignments.is_active', true)
        ->where('warehouses.is_active', true)
        ->first();
    }

    /**
     * Assign user to warehouse
     */
    public function assignUserToWarehouse($userId, $warehouseId, $data = [])
    {
        // Check if assignment already exists
        $existing = $this->where('user_id', $userId)
                        ->where('warehouse_id', $warehouseId)
                        ->first();

        // Ensure is_primary is integer (0 or 1) for validation
        if (isset($data['is_primary'])) {
            $data['is_primary'] = $data['is_primary'] ? 1 : 0;
        }
        
        // Ensure is_active is integer (0 or 1) for validation
        if (isset($data['is_active'])) {
            $data['is_active'] = $data['is_active'] ? 1 : 0;
        }

        if ($existing) {
            // Update existing assignment
            $updateData = array_merge([
                'is_active' => 1,  // Use integer 1 instead of boolean true
                'assigned_at' => date('Y-m-d H:i:s'),
                'assigned_by' => session()->get('user_id')
            ], $data);
            
            // Remove null values to avoid overwriting with null
            $updateData = array_filter($updateData, function($value) {
                return $value !== null;
            });
            
            // Always ensure assigned_at is updated (will always be different due to timestamp)
            // This prevents "no data to update" error when other values are the same
            $updateData['assigned_at'] = date('Y-m-d H:i:s');
            $updateData['assigned_by'] = session()->get('user_id');
            
            // Temporarily disable updateOnlyChanged to ensure update happens
            // This is needed because CodeIgniter might skip update if it thinks nothing changed
            $originalUpdateOnlyChanged = $this->updateOnlyChanged;
            $this->updateOnlyChanged = false;
            
            $result = $this->update($existing['id'], $updateData);
            
            // Restore original setting
            $this->updateOnlyChanged = $originalUpdateOnlyChanged;
            
            return $result;
        } else {
            // Create new assignment
            $insertData = array_merge([
                'user_id' => $userId,
                'warehouse_id' => $warehouseId,
                'is_active' => 1,  // Use integer 1 instead of boolean true
                'assigned_at' => date('Y-m-d H:i:s'),
                'assigned_by' => session()->get('user_id')
            ], $data);
            
            // Ensure role_id is set (required)
            if (!isset($insertData['role_id']) || empty($insertData['role_id'])) {
                // Get user's default role_id
                $userModel = new \App\Models\UserModel();
                $user = $userModel->find($userId);
                if ($user && isset($user['role_id'])) {
                    $insertData['role_id'] = $user['role_id'];
                } else {
                    log_message('error', 'User does not have a role_id: ' . $userId);
                    return false;
                }
            }
            
            return $this->insert($insertData) ? $this->getInsertID() : false;
        }
    }

    /**
     * Remove user from warehouse (soft delete by setting is_active = 0)
     */
    public function removeUserFromWarehouse($userId, $warehouseId)
    {
        // Find the assignment first
        $assignment = $this->where('user_id', $userId)
                          ->where('warehouse_id', $warehouseId)
                          ->first();
        
        if (!$assignment) {
            log_message('error', 'Assignment not found for user_id: ' . $userId . ', warehouse_id: ' . $warehouseId);
            return false;
        }
        
        // Check if already inactive
        if ((int)$assignment['is_active'] === 0) {
            return true; // Already removed
        }
        
        // Temporarily disable updateOnlyChanged to ensure update happens
        $originalUpdateOnlyChanged = $this->updateOnlyChanged;
        $this->updateOnlyChanged = false;
        
        // Use the assignment ID directly for update
        $updateData = [
            'is_active' => 0  // Use integer 0 instead of boolean false
        ];
        
        $result = $this->update($assignment['id'], $updateData);
        
        // Restore original setting
        $this->updateOnlyChanged = $originalUpdateOnlyChanged;
        
        if (!$result) {
            $errors = $this->errors();
            log_message('error', 'Failed to remove assignment: ' . json_encode($errors));
        }
        
        return $result;
    }

    /**
     * Set primary warehouse for user (ensures only one primary)
     */
    public function setPrimaryWarehouse($userId, $warehouseId)
    {
        // Find the assignment first
        $assignment = $this->where('user_id', $userId)
                          ->where('warehouse_id', $warehouseId)
                          ->first();
        
        if (!$assignment) {
            log_message('error', 'Assignment not found for setPrimaryWarehouse: user_id: ' . $userId . ', warehouse_id: ' . $warehouseId);
            return false;
        }
        
        // Check if already primary
        if ((int)$assignment['is_primary'] === 1) {
            // Already primary, just unset others
            $this->where('user_id', $userId)
                 ->where('warehouse_id !=', $warehouseId)
                 ->update(['is_primary' => 0]);
            return true;
        }
        
        // Temporarily disable updateOnlyChanged to ensure update happens
        $originalUpdateOnlyChanged = $this->updateOnlyChanged;
        $this->updateOnlyChanged = false;
        
        // First, unset all primary warehouses for this user
        $this->where('user_id', $userId)->update(['is_primary' => 0]);  // Use integer 0 instead of boolean false
        
        // Then set the specified warehouse as primary
        $result = $this->update($assignment['id'], ['is_primary' => 1]);  // Use integer 1 instead of boolean true
        
        // Restore original setting
        $this->updateOnlyChanged = $originalUpdateOnlyChanged;
        
        return $result;
    }
}

