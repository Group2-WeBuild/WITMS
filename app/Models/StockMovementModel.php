<?php

namespace App\Models;

use CodeIgniter\Model;

class StockMovementModel extends Model
{
    protected $table            = 'stock_movements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'reference_number',
        'material_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'movement_type',
        'quantity',
        'batch_number',
        'movement_date',
        'performed_by',
        'approved_by',
        'reference_id',
        'reference_type',
        'notes'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;      protected array $casts = [
        'id' => 'integer',
        'material_id' => 'integer',
        'from_warehouse_id' => '?integer',
        'to_warehouse_id' => '?integer',
        'quantity' => 'float',
        'performed_by' => 'integer',
        'approved_by' => '?integer',
        'reference_id' => '?integer',
        'movement_date' => 'datetime'
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
        'reference_number' => [
            'label' => 'Reference Number',
            'rules' => 'required|max_length[50]|is_unique[stock_movements.reference_number,id,{id}]'
        ],
        'material_id' => [
            'label' => 'Material',
            'rules' => 'required|integer|is_not_unique[materials.id]'
        ],
        'movement_type' => [
            'label' => 'Movement Type',
            'rules' => 'required|in_list[Receipt,Transfer,Issue,Adjustment,Return]'
        ],
        'quantity' => [
            'label' => 'Quantity',
            'rules' => 'required|decimal|greater_than[0]'
        ],
        'movement_date' => [
            'label' => 'Movement Date',
            'rules' => 'required|valid_date'
        ],
        'performed_by' => [
            'label' => 'Performed By',
            'rules' => 'required|integer|is_not_unique[users.id]'
        ]
    ];

    protected $validationMessages = [
        'reference_number' => [
            'is_unique' => 'This reference number already exists.'
        ]
    ];
    
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateReferenceNumber'];
    protected $afterInsert    = ['updateInventory'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Generate unique reference number
     */
    protected function generateReferenceNumber(array $data)
    {
        if (empty($data['data']['reference_number'])) {
            $type = strtoupper(substr($data['data']['movement_type'], 0, 3));
            $date = date('Ymd');
            
            // Find the next available number for today
            $lastMovement = $this->where('reference_number LIKE', "SM-{$type}-{$date}%")
                                ->orderBy('reference_number', 'DESC')
                                ->first();
            
            $nextNumber = 1;
            if ($lastMovement) {
                $lastNumber = (int) substr($lastMovement['reference_number'], -4);
                $nextNumber = $lastNumber + 1;
            }
            
            $data['data']['reference_number'] = "SM-{$type}-{$date}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
        
        return $data;
    }

    /**
     * Update inventory after stock movement
     */
    protected function updateInventory(array $data)
    {
        if (!$data['result']) {
            return $data;
        }

        $inventoryModel = new InventoryModel();
        $movementData = $data['data'];

        switch ($movementData['movement_type']) {
            case 'Receipt':
                // Add stock to destination warehouse
                if ($movementData['to_warehouse_id']) {
                    $inventoryModel->addStock([
                        'material_id' => $movementData['material_id'],
                        'warehouse_id' => $movementData['to_warehouse_id'],
                        'quantity' => $movementData['quantity'],
                        'batch_number' => $movementData['batch_number'] ?? null
                    ]);
                }
                break;

            case 'Issue':
                // Reduce stock from source warehouse
                if ($movementData['from_warehouse_id']) {
                    $inventoryModel->reduceStock(
                        $movementData['material_id'],
                        $movementData['from_warehouse_id'],
                        $movementData['quantity'],
                        $movementData['batch_number'] ?? null
                    );
                }
                break;

            case 'Transfer':
                // Reduce from source, add to destination
                if ($movementData['from_warehouse_id']) {
                    $inventoryModel->reduceStock(
                        $movementData['material_id'],
                        $movementData['from_warehouse_id'],
                        $movementData['quantity'],
                        $movementData['batch_number'] ?? null
                    );
                }
                if ($movementData['to_warehouse_id']) {
                    $inventoryModel->addStock([
                        'material_id' => $movementData['material_id'],
                        'warehouse_id' => $movementData['to_warehouse_id'],
                        'quantity' => $movementData['quantity'],
                        'batch_number' => $movementData['batch_number'] ?? null
                    ]);
                }
                break;

            case 'Adjustment':
                // Handled separately based on positive/negative adjustment
                break;

            case 'Return':
                // Add stock back to warehouse
                if ($movementData['to_warehouse_id']) {
                    $inventoryModel->addStock([
                        'material_id' => $movementData['material_id'],
                        'warehouse_id' => $movementData['to_warehouse_id'],
                        'quantity' => $movementData['quantity'],
                        'batch_number' => $movementData['batch_number'] ?? null
                    ]);
                }
                break;
        }

        return $data;
    }

    /**
     * Get movements with details
     */
    public function getMovementsWithDetails($id = null)
    {
        $builder = $this->select('
            stock_movements.*,
            materials.name as material_name,
            materials.code as material_code,
            units_of_measure.abbreviation as unit_abbr,
            w1.name as from_warehouse_name,
            w2.name as to_warehouse_name,
            u1.first_name as performed_by_first,
            u1.last_name as performed_by_last,
            u2.first_name as approved_by_first,
            u2.last_name as approved_by_last
        ')
        ->join('materials', 'materials.id = stock_movements.material_id')
        ->join('units_of_measure', 'units_of_measure.id = materials.unit_id')
        ->join('warehouses w1', 'w1.id = stock_movements.from_warehouse_id', 'left')
        ->join('warehouses w2', 'w2.id = stock_movements.to_warehouse_id', 'left')
        ->join('users u1', 'u1.id = stock_movements.performed_by')
        ->join('users u2', 'u2.id = stock_movements.approved_by', 'left');

        if ($id !== null) {
            return $builder->where('stock_movements.id', $id)->first();
        }

        return $builder->orderBy('stock_movements.movement_date', 'DESC')->findAll();
    }

    /**
     * Get movements by warehouse
     */
    public function getMovementsByWarehouse($warehouseId, $movementType = null)
    {
        $builder = $this->groupStart()
                       ->where('from_warehouse_id', $warehouseId)
                       ->orWhere('to_warehouse_id', $warehouseId)
                       ->groupEnd();

        if ($movementType) {
            $builder->where('movement_type', $movementType);
        }

        return $builder->orderBy('movement_date', 'DESC')->findAll();
    }

    /**
     * Get movements by material
     */
    public function getMovementsByMaterial($materialId, $limit = null)
    {
        $builder = $this->where('material_id', $materialId)
                       ->orderBy('movement_date', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Get movements by date range
     */
    public function getMovementsByDateRange($startDate, $endDate, $warehouseId = null)
    {
        $builder = $this->where('movement_date >=', $startDate)
                       ->where('movement_date <=', $endDate);

        if ($warehouseId) {
            $builder->groupStart()
                   ->where('from_warehouse_id', $warehouseId)
                   ->orWhere('to_warehouse_id', $warehouseId)
                   ->groupEnd();
        }

        return $builder->orderBy('movement_date', 'DESC')->findAll();
    }

    /**
     * Record stock receipt
     */
    public function recordReceipt($data)
    {
        $data['movement_type'] = 'Receipt';
        $data['movement_date'] = $data['movement_date'] ?? date('Y-m-d H:i:s');
        $data['from_warehouse_id'] = null;

        return $this->insert($data) ? $this->getInsertID() : false;
    }

    /**
     * Record stock issue
     */
    public function recordIssue($data)
    {
        $data['movement_type'] = 'Issue';
        $data['movement_date'] = $data['movement_date'] ?? date('Y-m-d H:i:s');
        $data['to_warehouse_id'] = null;

        return $this->insert($data) ? $this->getInsertID() : false;
    }

    /**
     * Record stock transfer
     */
    public function recordTransfer($data)
    {
        $data['movement_type'] = 'Transfer';
        $data['movement_date'] = $data['movement_date'] ?? date('Y-m-d H:i:s');

        return $this->insert($data) ? $this->getInsertID() : false;
    }

    /**
     * Get movement statistics
     */
    public function getMovementStats($warehouseId = null, $startDate = null, $endDate = null)
    {
        $builder = $this->db->table('stock_movements');

        if ($warehouseId) {
            $builder->groupStart()
                   ->where('from_warehouse_id', $warehouseId)
                   ->orWhere('to_warehouse_id', $warehouseId)
                   ->groupEnd();
        }

        if ($startDate && $endDate) {
            $builder->where('movement_date >=', $startDate)
                   ->where('movement_date <=', $endDate);
        }

        $stats = [
            'total_movements' => $builder->countAllResults(false),
            'receipts' => (clone $builder)->where('movement_type', 'Receipt')->countAllResults(),
            'issues' => (clone $builder)->where('movement_type', 'Issue')->countAllResults(),
            'transfers' => (clone $builder)->where('movement_type', 'Transfer')->countAllResults(),
            'adjustments' => (clone $builder)->where('movement_type', 'Adjustment')->countAllResults(),
            'returns' => (clone $builder)->where('movement_type', 'Return')->countAllResults()
        ];

        return $stats;
    }
}
