<?php

namespace App\Models;

use CodeIgniter\Model;

class WarehouseModel extends Model
{
    protected $table            = 'warehouses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
      protected $allowedFields    = [
        'name',
        'code',
        'warehouse_location_id',
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
            'rules' => 'required|max_length[100]|regex_match[/^[A-Za-z\s]+$/]|is_unique[warehouses.name,id,{id}]'
        ],        
        'code' => [
            'label' => 'Warehouse Code',
            'rules' => 'required|max_length[20]|regex_match[/^WH-?\d{3,}$/]|is_unique[warehouses.code,id,{id}]'
        ],
        'warehouse_location_id' => [
            'label' => 'Warehouse Location',
            'rules' => 'permit_empty|is_natural_no_zero|is_not_unique[warehouse_locations.id]'
        ],
        'capacity' => [
            'label' => 'Capacity',
            'rules' => 'permit_empty|numeric|greater_than_equal_to[0]'
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
            'is_unique' => 'This warehouse name already exists.',
            'regex_match' => 'Warehouse name can only contain letters and spaces. Special characters are not allowed.'
        ],
        'code' => [
            'is_unique' => 'This warehouse code already exists.',
            'regex_match' => 'Warehouse code must be in format WH-001 or WH001 (e.g., WH-001, WH001, WH-123). Special characters are not allowed.'
        ],
        'capacity' => [
            'numeric' => 'Capacity must be a number.',
            'greater_than_equal_to' => 'Capacity must be a positive number or zero.'
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
     * Get active warehouses only (excludes soft-deleted)
     */
    public function getActiveWarehouses()
    {
        return $this->where('is_active', 1)->findAll();
    }
    
    /**
     * Get warehouses including soft-deleted ones
     */
    public function getAllWarehousesWithDeleted()
    {
        return $this->withDeleted()->findAll();
    }
    
    /**
     * Restore a soft-deleted warehouse
     */
    public function restoreWarehouse(int $warehouseId): bool
    {
        return $this->withDeleted()->update($warehouseId, ['deleted_at' => null]);
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

    /**
     * Get warehouses with location details
     */
    public function getWarehousesWithLocations()
    {
        return $this->select('warehouses.*, 
            warehouse_locations.street_address,
            warehouse_locations.barangay,
            warehouse_locations.city,
            warehouse_locations.province,
            warehouse_locations.region,
            warehouse_locations.postal_code,
            warehouse_locations.country,
            warehouse_locations.latitude,
            warehouse_locations.longitude')
            ->join('warehouse_locations', 'warehouse_locations.id = warehouses.warehouse_location_id', 'left')
            ->findAll();
    }

    /**
     * Get single warehouse with location details
     */
    public function getWarehouseWithLocation(int $warehouseId)
    {
        return $this->select('warehouses.*, 
            warehouse_locations.street_address,
            warehouse_locations.barangay,
            warehouse_locations.city,
            warehouse_locations.province,
            warehouse_locations.region,
            warehouse_locations.postal_code,
            warehouse_locations.country,
            warehouse_locations.latitude,
            warehouse_locations.longitude')
            ->join('warehouse_locations', 'warehouse_locations.id = warehouses.warehouse_location_id', 'left')
            ->where('warehouses.id', $warehouseId)
            ->first();
    }

    /**
     * Get formatted warehouse address
     */
    public function getFormattedAddress(int $warehouseId): ?string
    {
        $warehouse = $this->getWarehouseWithLocation($warehouseId);
        
        if (!$warehouse || !$warehouse['city']) {
            return null;
        }

        $parts = [];
        if (!empty($warehouse['street_address'])) $parts[] = $warehouse['street_address'];
        if (!empty($warehouse['barangay'])) $parts[] = 'Brgy. ' . $warehouse['barangay'];
        if (!empty($warehouse['city'])) $parts[] = $warehouse['city'];
        if (!empty($warehouse['province'])) $parts[] = $warehouse['province'];
        if (!empty($warehouse['region'])) $parts[] = $warehouse['region'];
        if (!empty($warehouse['postal_code'])) $parts[] = $warehouse['postal_code'];
        if (!empty($warehouse['country'])) $parts[] = $warehouse['country'];

        return implode(', ', $parts);
    }
}
