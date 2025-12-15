<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table            = 'materials';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'code',
        'qrcode',
        'category_id',
        'unit_id',
        'description',
        'reorder_level',
        'reorder_quantity',
        'unit_cost',
        'is_perishable',
        'shelf_life_days',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;    protected array $casts = [
        'id' => 'integer',
        'category_id' => 'integer',
        'unit_id' => 'integer',
        'reorder_level' => 'float',
        'reorder_quantity' => 'float',
        'unit_cost' => 'float',
        'is_perishable' => 'boolean',
        'shelf_life_days' => '?integer',
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
        'name' => [
            'label' => 'Material Name',
            'rules' => 'required|max_length[200]'
        ],
        'code' => [
            'label' => 'Material Code',
            'rules' => 'required|max_length[50]|is_unique[materials.code,id,{id}]'
        ],
        'qrcode' => [
            'label' => 'QR Code',
            'rules' => 'permit_empty|max_length[100]|is_unique[materials.qrcode,id,{id}]'
        ],
        'category_id' => [
            'label' => 'Category',
            'rules' => 'required|integer|greater_than[0]|is_not_unique[material_categories.id]'
        ],
        'unit_id' => [
            'label' => 'Unit of Measure',
            'rules' => 'required|integer|greater_than[0]|is_not_unique[units_of_measure.id]'
        ],
        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty|string'
        ],
        'reorder_level' => [
            'label' => 'Reorder Level',
            'rules' => 'required|decimal|greater_than_equal_to[0]'
        ],
        'reorder_quantity' => [
            'label' => 'Reorder Quantity',
            'rules' => 'required|decimal|greater_than[0]'
        ],
        'unit_cost' => [
            'label' => 'Unit Cost',
            'rules' => 'required|decimal|greater_than_equal_to[0]'
        ],
        'is_perishable' => [
            'label' => 'Is Perishable',
            'rules' => 'permit_empty|in_list[0,1]'
        ],
        'shelf_life_days' => [
            'label' => 'Shelf Life (Days)',
            'rules' => 'permit_empty|integer|greater_than[0]'
        ],
        'is_active' => [
            'label' => 'Is Active',
            'rules' => 'permit_empty'
        ]
    ];

    protected $validationMessages = [
        'code' => [
            'is_unique' => 'This material code already exists in the system.'
        ],
        'qrcode' => [
            'is_unique' => 'This QR code already exists in the system.'
        ],
        'category_id' => [
            'is_not_unique' => 'The selected category does not exist.'
        ],
        'unit_id' => [
            'is_not_unique' => 'The selected unit of measure does not exist.'
        ]
    ];
    
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateCode', 'setActiveDefault'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get materials with their category and unit information
     */
    public function getMaterialsWithDetails($id = null)
    {        
        $builder = $this->select('
            materials.*,
            material_categories.name as category_name,
            material_categories.code as category_code,
            units_of_measure.name as unit_name,
            units_of_measure.abbreviation as unit_abbreviation
        ')
        ->join('material_categories', 'material_categories.id = materials.category_id')
        ->join('units_of_measure', 'units_of_measure.id = materials.unit_id');

        if ($id !== null) {
            return $builder->where('materials.id', $id)->first();
        }

        return $builder->findAll();
    }

    /**
     * Get active materials only
     */
    public function getActiveMaterials()
    {
        return $this->where('is_active', true)->findAll();
    }

    /**
     * Get materials by category
     */
    public function getMaterialsByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)
                   ->where('is_active', true)
                   ->findAll();
    }

    /**
     * Get materials that need reordering
     */
    public function getMaterialsNeedingReorder()
    {
        return $this->select('
            materials.*,
            material_categories.name as category_name,
            units_of_measure.name as unit_name,
            COALESCE(SUM(inventory.quantity), 0) as current_stock
        ')
        ->join('material_categories', 'material_categories.id = materials.category_id')
        ->join('units_of_measure', 'units_of_measure.id = materials.unit_id')
        ->join('inventory', 'inventory.material_id = materials.id', 'left')
        ->where('materials.is_active', true)
        ->groupBy('materials.id')
        ->having('current_stock <=', 'materials.reorder_level', false)
        ->findAll();
    }

    /**
     * Search materials by name or code
     */
    public function searchMaterials($searchTerm)
    {
        return $this->groupStart()
                   ->like('name', $searchTerm)
                   ->orLike('code', $searchTerm)
                   ->orLike('qrcode', $searchTerm)
                   ->groupEnd()
                   ->where('is_active', true)
                   ->findAll();
    }

    /**
     * Get perishable materials
     */
    public function getPerishableMaterials()
    {
        return $this->where('is_perishable', true)
                   ->where('is_active', true)
                   ->findAll();
    }

    /**
     * Create a new material with validation
     */
    public function createMaterial($data)
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        
        if ($this->insert($data)) {
            return $this->getInsertID();
        }

        return false;
    }    
    
    /**
     * Update material with validation
     */
    public function updateMaterial($id, $data)
    {
        $this->validationData = array_merge($data, ['id' => $id]);
        
        $result = $this->update($id, $data);
        
        // Clear validation data after use
        $this->validationData = [];
        
        return $result;
    }

    /**
     * Soft delete material (set as inactive)
     */
    public function deactivateMaterial($id)
    {
        return $this->update($id, ['is_active' => 0]);
    }

    /**
     * Activate material
     */
    public function activateMaterial($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Set default value for is_active field
     */
    protected function setActiveDefault(array $data)
    {
        if (!isset($data['data']['is_active'])) {
            $data['data']['is_active'] = 1; // Default to active
        }
        
        return $data;
    }

    /**
     * Generate unique material code if not provided
     */
    protected function generateCode(array $data)
    {
        if (empty($data['data']['code'])) {
            // Get category code for prefix
            $categoryModel = new \App\Models\MaterialCategoryModel();
            $category = $categoryModel->find($data['data']['category_id']);
            
            if ($category) {
                $prefix = strtoupper($category['code']);
                
                // Find the next available number
                $lastMaterial = $this->where('code LIKE', $prefix . '%')
                                   ->orderBy('code', 'DESC')
                                   ->first();
                
                $nextNumber = 1;
                if ($lastMaterial) {
                    $lastNumber = (int) substr($lastMaterial['code'], strlen($prefix));
                    $nextNumber = $lastNumber + 1;
                }
                
                $data['data']['code'] = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        }
        
        return $data;
    }

    /**
     * Get material statistics
     */
    public function getMaterialStats()
    {
        $stats = [];
        
        $stats['total_materials'] = $this->countAll();
        $stats['active_materials'] = $this->where('is_active', true)->countAllResults();
        $stats['inactive_materials'] = $this->where('is_active', false)->countAllResults();
        $stats['perishable_materials'] = $this->where('is_perishable', true)
                                             ->where('is_active', true)
                                             ->countAllResults();
        return $stats;
    }
}