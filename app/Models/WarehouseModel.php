<?php

namespace App\Models;

use CodeIgniter\Model;

class WarehouseModel extends Model
{
    protected $table            = 'warehouses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'name',
        'code',
        'location',
        'capacity',
        'manager_id',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'name' => [
            'label' => 'Warehouse Name',
            'rules' => 'required|max_length[100]|is_unique[warehouses.name,id,{id}]'
        ],
        'code' => [
            'label' => 'Warehouse Code',
            'rules' => 'required|max_length[20]|is_unique[warehouses.code,id,{id}]|alpha_dash'
        ],
        'location' => [
            'label' => 'Location',
            'rules' => 'required|max_length[255]'
        ],
        'capacity' => [
            'label' => 'Capacity',
            'rules' => 'permit_empty|decimal'
        ],
        'manager_id' => [
            'label' => 'Manager',
            'rules' => 'permit_empty|is_natural_no_zero'
        ],
        'is_active' => [
            'label' => 'Active Status',
            'rules' => 'permit_empty|in_list[0,1]'
        ]
    ];
    
    protected $validationMessages = [
        'name' => [
            'is_unique' => 'This warehouse name already exists.'
        ],
        'code' => [
            'is_unique' => 'This warehouse code already exists.'
        ]
    ];
    
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get active warehouses only
     */
    public function getActiveWarehouses()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Get warehouse by code
     */
    public function getWarehouseByCode(string $code)
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Get warehouses with manager details
     */
    public function getWarehousesWithManagers()
    {
        return $this->select('warehouses.*, users.first_name, users.last_name, users.email as manager_email')
                    ->join('users', 'users.id = warehouses.manager_id', 'left')
                    ->where('warehouses.is_active', 1)
                    ->findAll();
    }

    /**
     * Get warehouse with department count
     */
    public function getWarehouseWithDepartmentCount(int $warehouseId)
    {
        return $this->select('warehouses.*, COUNT(departments.id) as department_count')
                    ->join('departments', 'departments.warehouse_id = warehouses.id', 'left')
                    ->where('warehouses.id', $warehouseId)
                    ->groupBy('warehouses.id')
                    ->first();
    }

    /**
     * Get all warehouses with department counts
     */
    public function getWarehousesWithDepartmentCounts()
    {
        return $this->select('warehouses.*, COUNT(departments.id) as department_count')
                    ->join('departments', 'departments.warehouse_id = warehouses.id', 'left')
                    ->where('warehouses.is_active', 1)
                    ->groupBy('warehouses.id')
                    ->findAll();
    }

    /**
     * Assign manager to warehouse
     */
    public function assignManager(int $warehouseId, int $managerId): bool
    {
        return $this->update($warehouseId, ['manager_id' => $managerId]);
    }

    /**
     * Remove manager from warehouse
     */
    public function removeManager(int $warehouseId): bool
    {
        return $this->update($warehouseId, ['manager_id' => null]);
    }

    /**
     * Activate/Deactivate warehouse
     */
    public function toggleWarehouseStatus(int $warehouseId, bool $status): bool
    {
        return $this->update($warehouseId, ['is_active' => $status]);
    }

    /**
     * Create new warehouse
     */
    public function createWarehouse(array $warehouseData): bool
    {
        // Set default values
        $warehouseData['is_active'] = $warehouseData['is_active'] ?? 1;
        
        return $this->insert($warehouseData);
    }

    /**
     * Update warehouse information
     */
    public function updateWarehouse(int $warehouseId, array $warehouseData): bool
    {
        return $this->update($warehouseId, $warehouseData);
    }    /**
     * Get warehouse statistics
     */
    public function getWarehouseStats(): array
    {
        $builder = $this->db->table($this->table);
        
        $stats = [
            'total_warehouses' => $builder->countAll(),
            'active_warehouses' => $this->where('is_active', 1)->countAllResults(),
            'inactive_warehouses' => $this->where('is_active', 0)->countAllResults(),
            'warehouses_with_managers' => $this->where('manager_id IS NOT NULL')
                                              ->where('is_active', 1)
                                              ->countAllResults(),
            'warehouses_without_managers' => $this->where('manager_id IS NULL')
                                                  ->where('is_active', 1)
                                                  ->countAllResults(),
        ];

        return $stats;
    }

    /**
     * Search warehouses by name, code, or location
     */
    public function searchWarehouses(string $keyword)
    {
        return $this->groupStart()
                    ->like('name', $keyword)
                    ->orLike('code', $keyword)
                    ->orLike('location', $keyword)
                    ->groupEnd()
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get warehouses needing managers
     */
    public function getWarehousesNeedingManagers()
    {
        return $this->where('manager_id IS NULL')
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Check if warehouse has capacity
     */
    public function hasCapacity(int $warehouseId): bool
    {
        $warehouse = $this->find($warehouseId);
        return $warehouse && !empty($warehouse['capacity']) && $warehouse['capacity'] > 0;
    }

    /**
     * Get warehouse capacity utilization
     */
    public function getCapacityUtilization(int $warehouseId): array
    {
        $warehouse = $this->find($warehouseId);
        
        if (!$warehouse) {
            return [
                'total_capacity' => 0,
                'used_capacity' => 0,
                'available_capacity' => 0,
                'utilization_percentage' => 0
            ];
        }

        // This would need integration with inventory to calculate actual usage
        // For now, returning structure
        return [
            'total_capacity' => $warehouse['capacity'] ?? 0,
            'used_capacity' => 0, // TODO: Calculate from inventory
            'available_capacity' => $warehouse['capacity'] ?? 0,
            'utilization_percentage' => 0
        ];
    }
}
