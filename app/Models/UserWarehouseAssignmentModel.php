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

        if ($existing) {
            // Update existing assignment
            $updateData = array_merge([
                'is_active' => true,
                'assigned_at' => date('Y-m-d H:i:s'),
                'assigned_by' => session()->get('user_id')
            ], $data);
            
            return $this->update($existing['id'], $updateData);
        } else {
            // Create new assignment
            $insertData = array_merge([
                'user_id' => $userId,
                'warehouse_id' => $warehouseId,
                'is_active' => true,
                'assigned_at' => date('Y-m-d H:i:s'),
                'assigned_by' => session()->get('user_id')
            ], $data);
            
            return $this->insert($insertData) ? $this->getInsertID() : false;
        }
    }

    /**
     * Remove user from warehouse (soft delete by setting is_active = false)
     */
    public function removeUserFromWarehouse($userId, $warehouseId)
    {
        return $this->where('user_id', $userId)
                   ->where('warehouse_id', $warehouseId)
                   ->update(['is_active' => false]);
    }

    /**
     * Set primary warehouse for user (ensures only one primary)
     */
    public function setPrimaryWarehouse($userId, $warehouseId)
    {
        // First, unset all primary warehouses for this user
        $this->where('user_id', $userId)->update(['is_primary' => false]);
        
        // Then set the specified warehouse as primary
        return $this->where('user_id', $userId)
                   ->where('warehouse_id', $warehouseId)
                   ->update(['is_primary' => true]);
    }
}

