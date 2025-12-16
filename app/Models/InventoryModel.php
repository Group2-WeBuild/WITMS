<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table            = 'inventory';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'material_id',
        'warehouse_id',
        'batch_number',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'location_in_warehouse',
        'expiration_date',
        'last_counted_date'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;    protected array $casts = [
        'id' => 'integer',
        'material_id' => 'integer',
        'warehouse_id' => 'integer',
        'quantity' => 'float',
        'reserved_quantity' => 'float',
        'available_quantity' => 'float'
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
        'material_id' => [
            'label' => 'Material',
            'rules' => 'required|integer|is_not_unique[materials.id]'
        ],
        'warehouse_id' => [
            'label' => 'Warehouse',
            'rules' => 'required|integer|is_not_unique[warehouses.id]'
        ],
        'quantity' => [
            'label' => 'Quantity',
            'rules' => 'required|decimal|greater_than_equal_to[0]'
        ],
        'reserved_quantity' => [
            'label' => 'Reserved Quantity',
            'rules' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[quantity]'
        ],
        'batch_number' => [
            'label' => 'Batch Number',
            'rules' => 'permit_empty|max_length[50]'
        ],
        'location_in_warehouse' => [
            'label' => 'Location',
            'rules' => 'permit_empty|max_length[100]'
        ]
    ];

    protected $validationMessages = [
        'material_id' => [
            'is_not_unique' => 'The selected material does not exist.'
        ],
        'warehouse_id' => [
            'is_not_unique' => 'The selected warehouse does not exist.'
        ]
    ];
    
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateBatchNumber', 'calculateAvailable'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['generateBatchNumber', 'calculateAvailable'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Calculate available quantity before insert/update
     */
    protected function calculateAvailable(array $data)
    {
        // For updates, get existing record to ensure we have both values
        if (isset($data['id']) && is_array($data['id']) && isset($data['id'][0])) {
            $existing = $this->find($data['id'][0]);
            if ($existing) {
                // Use provided values or fall back to existing values
                $quantity = floatval($data['data']['quantity'] ?? $existing['quantity'] ?? 0);
                $reserved = floatval($data['data']['reserved_quantity'] ?? $existing['reserved_quantity'] ?? 0);
            } else {
                // If record doesn't exist, use provided values or defaults
                $quantity = floatval($data['data']['quantity'] ?? 0);
                $reserved = floatval($data['data']['reserved_quantity'] ?? 0);
            }
        } else {
            // For inserts, use provided values or defaults
            $quantity = floatval($data['data']['quantity'] ?? 0);
            $reserved = floatval($data['data']['reserved_quantity'] ?? 0);
        }
        
        // Ensure reserved_quantity never exceeds quantity
        if ($reserved > $quantity) {
            $reserved = $quantity;
            $data['data']['reserved_quantity'] = $reserved;
        }
        
        // Calculate available_quantity and ensure it's never negative
        $available = $quantity - $reserved;
        $data['data']['available_quantity'] = max(0, $available);
        
        return $data;
    }

    /**
     * Generate batch number if not provided
     */
    protected function generateBatchNumber(array $data)
    {
        // Only generate if batch_number is empty
        if (empty($data['data']['batch_number'])) {
            $date = date('Ymd');
            
            // For updates, get existing record to retrieve material_id and warehouse_id if not in data
            $materialId = $data['data']['material_id'] ?? null;
            $warehouseId = $data['data']['warehouse_id'] ?? null;
            
            // If updating and IDs not in data, get from existing record
            if (isset($data['id']) && is_array($data['id']) && isset($data['id'][0])) {
                $existing = $this->find($data['id'][0]);
                if ($existing) {
                    $materialId = $materialId ?? $existing['material_id'] ?? null;
                    $warehouseId = $warehouseId ?? $existing['warehouse_id'] ?? null;
                }
            }
            
            // Get material code if available for better batch identification
            $materialCode = '';
            if ($materialId) {
                $materialModel = new \App\Models\MaterialModel();
                $material = $materialModel->find($materialId);
                if ($material && !empty($material['code'])) {
                    $materialCode = strtoupper(substr($material['code'], 0, 6));
                }
            }
            
            // Get warehouse code if available
            $warehouseCode = '';
            if ($warehouseId) {
                $warehouseModel = new \App\Models\WarehouseModel();
                $warehouse = $warehouseModel->find($warehouseId);
                if ($warehouse && !empty($warehouse['code'])) {
                    $warehouseCode = strtoupper(substr($warehouse['code'], 0, 3));
                }
            }
            
            // Build prefix
            $prefix = 'BATCH';
            if ($materialCode && $warehouseCode) {
                $prefix = $warehouseCode . '-' . $materialCode;
            } elseif ($warehouseCode) {
                $prefix = $warehouseCode . '-BATCH';
            } elseif ($materialCode) {
                $prefix = $materialCode . '-BATCH';
            }
            
            // Find the next available number for today with this prefix
            $lastBatch = $this->where('batch_number LIKE', "{$prefix}-{$date}%")
                             ->orderBy('batch_number', 'DESC')
                             ->first();
            
            $nextNumber = 1;
            if ($lastBatch && !empty($lastBatch['batch_number'])) {
                $lastNumber = (int) substr($lastBatch['batch_number'], -4);
                $nextNumber = $lastNumber + 1;
            }
            
            $data['data']['batch_number'] = "{$prefix}-{$date}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
        
        return $data;
    }

    /**
     * Recalculate available quantity for all inventory items
     * This fixes any incorrect available_quantity values in the database
     */
    public function recalculateAllAvailableQuantities()
    {
        $allInventory = $this->findAll();
        $updated = 0;
        
        foreach ($allInventory as $item) {
            $quantity = floatval($item['quantity'] ?? 0);
            $reserved = floatval($item['reserved_quantity'] ?? 0);
            
            // Ensure reserved doesn't exceed quantity
            if ($reserved > $quantity) {
                $reserved = $quantity;
                // Update reserved_quantity if it was too high
                $this->update($item['id'], [
                    'reserved_quantity' => $reserved
                ]);
            }
            
            // Calculate available (never negative)
            $correctAvailable = max(0, $quantity - $reserved);
            
            // Only update if the value is incorrect
            if (abs(floatval($item['available_quantity'] ?? 0) - $correctAvailable) > 0.01) {
                $this->update($item['id'], [
                    'available_quantity' => $correctAvailable
                ]);
                $updated++;
            }
        }
        
        return $updated;
    }

    /**
     * Get inventory with full details
     */
    public function getInventoryWithDetails($id = null)
    {        $builder = $this->select('
            inventory.*,
            materials.name as material_name,
            materials.code as material_code,
            materials.qrcode as material_qrcode,
            materials.unit_cost,
            materials.reorder_level,
            material_categories.name as category_name,
            units_of_measure.name as unit_name,
            units_of_measure.abbreviation as unit_abbr,
            warehouses.name as warehouse_name,
            warehouses.code as warehouse_code
        ')
        ->join('materials', 'materials.id = inventory.material_id')
        ->join('material_categories', 'material_categories.id = materials.category_id')
        ->join('units_of_measure', 'units_of_measure.id = materials.unit_id')
        ->join('warehouses', 'warehouses.id = inventory.warehouse_id');

        if ($id !== null) {
            return $builder->where('inventory.id', $id)->first();
        }

        return $builder->findAll();
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems($warehouseId = null)
    {
        $builder = $this->select('
            inventory.*,
            materials.name as material_name,
            materials.code as material_code,
            materials.reorder_level,
            materials.reorder_quantity,
            materials.unit_cost,
            material_categories.name as category_name,
            warehouses.name as warehouse_name,
            units_of_measure.abbreviation as unit_abbr
        ')
        ->join('materials', 'materials.id = inventory.material_id')
        ->join('material_categories', 'material_categories.id = materials.category_id')
        ->join('warehouses', 'warehouses.id = inventory.warehouse_id')
        ->join('units_of_measure', 'units_of_measure.id = materials.unit_id')
        ->where('inventory.available_quantity <=', 'materials.reorder_level', false);

        if ($warehouseId !== null) {
            $builder->where('inventory.warehouse_id', $warehouseId);
        }

        return $builder->findAll();
    }

    /**
     * Get expiring items
     */
    public function getExpiringItems($days = 30, $warehouseId = null)
    {
        $builder = $this->select('
            inventory.*,
            materials.name as material_name,
            materials.code as material_code,
            materials.unit_cost,
            material_categories.name as category_name,
            warehouses.name as warehouse_name,
            units_of_measure.abbreviation as unit_abbr
        ')
        ->join('materials', 'materials.id = inventory.material_id')
        ->join('material_categories', 'material_categories.id = materials.category_id')
        ->join('warehouses', 'warehouses.id = inventory.warehouse_id')
        ->join('units_of_measure', 'units_of_measure.id = materials.unit_id')
        ->where('inventory.expiration_date IS NOT NULL')
        ->where('inventory.expiration_date <=', date('Y-m-d', strtotime("+{$days} days")))
        ->where('inventory.expiration_date >=', date('Y-m-d'));

        if ($warehouseId !== null) {
            $builder->where('inventory.warehouse_id', $warehouseId);
        }

        return $builder->orderBy('inventory.expiration_date', 'ASC')->findAll();
    }

    /**
     * Get inventory valuation by category
     */
    public function getInventoryValuation($warehouseId = null)
    {
        $builder = $this->select('
            materials.id as material_id,
            materials.name as material_name,
            materials.code as material_code,
            materials.unit_cost,
            material_categories.name as category_name,
            units_of_measure.name as unit_name,
            SUM(inventory.available_quantity) as total_quantity,
            SUM(inventory.available_quantity * materials.unit_cost) as total_value
        ')
        ->join('materials', 'materials.id = inventory.material_id')
        ->join('material_categories', 'material_categories.id = materials.category_id')
        ->join('units_of_measure', 'units_of_measure.id = materials.unit_id')
        ->where('inventory.available_quantity >', 0)
        ->groupBy('materials.id, material_categories.id, units_of_measure.id');

        if ($warehouseId !== null) {
            $builder->where('inventory.warehouse_id', $warehouseId);
        }

        return $builder->findAll();
    }

    /**
     * Add stock
     */
    public function addStock($data)
    {
        // Check if inventory record exists
        $existing = $this->where('material_id', $data['material_id'])
                        ->where('warehouse_id', $data['warehouse_id'])
                        ->where('batch_number', $data['batch_number'] ?? null)
                        ->first();

        if ($existing) {
            // Update existing record
            $newQuantity = $existing['quantity'] + $data['quantity'];
            return $this->update($existing['id'], [
                'quantity' => $newQuantity,
                'location_in_warehouse' => $data['location_in_warehouse'] ?? $existing['location_in_warehouse'],
                'expiration_date' => $data['expiration_date'] ?? $existing['expiration_date']
            ]);
        } else {
            // Create new record
            return $this->insert($data);
        }
    }

    /**
     * Reduce stock
     */
    public function reduceStock($materialId, $warehouseId, $quantity, $batchNumber = null)
    {
        $builder = $this->where('material_id', $materialId)
                       ->where('warehouse_id', $warehouseId);

        if ($batchNumber) {
            $builder->where('batch_number', $batchNumber);
        }

        $inventory = $builder->first();

        if (!$inventory) {
            return false;
        }

        if ($inventory['available_quantity'] < $quantity) {
            return false; // Insufficient stock
        }

        $newQuantity = $inventory['quantity'] - $quantity;
        return $this->update($inventory['id'], ['quantity' => $newQuantity]);
    }

    /**
     * Reserve stock
     */
    public function reserveStock($inventoryId, $quantity)
    {
        $inventory = $this->find($inventoryId);

        if (!$inventory) {
            return false;
        }

        $currentQuantity = floatval($inventory['quantity'] ?? 0);
        $currentReserved = floatval($inventory['reserved_quantity'] ?? 0);
        $available = $currentQuantity - $currentReserved;
        
        // Check if there's enough available stock
        if ($available < $quantity) {
            return false;
        }

        $newReserved = $currentReserved + $quantity;
        
        // Double-check that reserved doesn't exceed quantity
        if ($newReserved > $currentQuantity) {
            $newReserved = $currentQuantity;
        }
        
        return $this->update($inventoryId, ['reserved_quantity' => $newReserved]);
    }

    /**
     * Release reserved stock
     */
    public function releaseReservedStock($inventoryId, $quantity)
    {
        $inventory = $this->find($inventoryId);

        if (!$inventory || $inventory['reserved_quantity'] < $quantity) {
            return false;
        }

        $newReserved = $inventory['reserved_quantity'] - $quantity;
        return $this->update($inventoryId, ['reserved_quantity' => $newReserved]);
    }

    /**
     * Get total inventory value
     */
    public function getTotalInventoryValue($warehouseId = null)
    {
        $builder = $this->select('SUM(inventory.quantity * materials.unit_cost) as total_value')
            ->join('materials', 'materials.id = inventory.material_id');

        if ($warehouseId !== null) {
            $builder->where('inventory.warehouse_id', $warehouseId);
        }

        $result = $builder->first();
        return $result['total_value'] ?? 0;
    }    /**
     * Get inventory by material ID
     */
    public function getInventoryByMaterial($materialId)
    {
        return $this->select('
                inventory.*,
                materials.name as material_name,
                materials.code as material_code,
                warehouses.name as warehouse_name,
                warehouses.code as warehouse_code,
                inventory.location_in_warehouse as location_name,
                units_of_measure.name as unit_name,
                units_of_measure.abbreviation as unit_abbr
            ')
            ->join('materials', 'materials.id = inventory.material_id')
            ->join('warehouses', 'warehouses.id = inventory.warehouse_id')
            ->join('units_of_measure', 'units_of_measure.id = materials.unit_id', 'left')
            ->where('inventory.material_id', $materialId)
            ->findAll();
    }

    /**
     * Get inventory by warehouse ID
     */
    public function getInventoryByWarehouse($warehouseId)
    {
        return $this->select('
                inventory.*,
                materials.name as material_name,
                materials.code as material_code,
                warehouses.name as warehouse_name,
                warehouses.code as warehouse_code,
                inventory.location_in_warehouse as location_name,
                units_of_measure.name as unit_name,
                units_of_measure.abbreviation as unit_abbr
            ')
            ->join('materials', 'materials.id = inventory.material_id')
            ->join('warehouses', 'warehouses.id = inventory.warehouse_id')
            ->join('units_of_measure', 'units_of_measure.id = materials.unit_id', 'left')
            ->where('inventory.warehouse_id', $warehouseId)
            ->findAll();
    }

    /**
     * Get inventory statistics
     */
    public function getInventoryStats($warehouseId = null)
    {
        $builder = $this->db->table('inventory');

        if ($warehouseId !== null) {
            $builder->where('warehouse_id', $warehouseId);
        }

        // Count total items
        $totalItems = $builder->countAllResults(false);
        
        // Get total quantity
        $builderQty = $this->db->table('inventory');
        if ($warehouseId !== null) {
            $builderQty->where('warehouse_id', $warehouseId);
        }
        $totalQty = $builderQty->selectSum('quantity')->get()->getRow()->quantity ?? 0;

        $stats = [
            'total_items' => $totalItems,
            'total_quantity' => $totalQty,
            'total_value' => $this->getTotalInventoryValue($warehouseId),
            'low_stock' => count($this->getLowStockItems($warehouseId)),
            'low_stock_items' => count($this->getLowStockItems($warehouseId)),
            'expiring' => count($this->getExpiringItems(30, $warehouseId)),
            'expiring_soon' => count($this->getExpiringItems(30, $warehouseId)),
            'expiring_items' => count($this->getExpiringItems(30, $warehouseId))
        ];

        return $stats;
    }
}
