<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialCategoryModel extends Model
{
    protected $table            = 'material_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'code',
        'description',
        'parent_id',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;    protected array $casts = [
        'id' => 'integer',
        'parent_id' => '?integer',
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
            'label' => 'Category Name',
            'rules' => 'required|max_length[100]'
        ],
        'code' => [
            'label' => 'Category Code',
            'rules' => 'required|max_length[20]|is_unique[material_categories.code,id,{id}]'
        ],
        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty|string'
        ],
        'parent_id' => [
            'label' => 'Parent Category',
            'rules' => 'permit_empty|integer|is_not_unique[material_categories.id]'
        ],
        'is_active' => [
            'label' => 'Is Active',
            'rules' => 'permit_empty|in_list[0,1]'
        ]
    ];

    protected $validationMessages = [
        'code' => [
            'is_unique' => 'This category code already exists in the system.'
        ],
        'parent_id' => [
            'is_not_unique' => 'The selected parent category does not exist.'
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
     * Get all active categories
     */
    public function getActiveCategories()
    {
        return $this->where('is_active', true)->findAll();
    }

    /**
     * Get categories with hierarchy (parent-child structure)
     */
    public function getCategoriesWithHierarchy()
    {
        return $this->select('
            c1.id,
            c1.code,
            c1.name,
            c1.description,
            c1.parent_id,
            c1.is_active,
            c1.created_at,
            c1.updated_at,
            c2.name as parent_name
        ')
        ->from('material_categories c1')
        ->join('material_categories c2', 'c2.id = c1.parent_id', 'left')
        ->where('c1.is_active', true)
        ->groupBy('c1.id')
        ->orderBy('c1.parent_id ASC, c1.name ASC')
        ->findAll();
    }

    /**
     * Get root categories (categories with no parent)
     */
    public function getRootCategories()
    {
        return $this->where('parent_id IS NULL')
                   ->where('is_active', true)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get child categories of a parent
     */
    public function getChildCategories($parentId)
    {
        return $this->where('parent_id', $parentId)
                   ->where('is_active', true)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get category tree (nested structure)
     */
    public function getCategoryTree()
    {
        $rootCategories = $this->getRootCategories();
        
        foreach ($rootCategories as &$category) {
            $category['children'] = $this->buildCategoryTree($category['id']);
        }
        
        return $rootCategories;
    }

    /**
     * Build category tree recursively
     */
    private function buildCategoryTree($parentId)
    {
        $children = $this->getChildCategories($parentId);
        
        foreach ($children as &$child) {
            $child['children'] = $this->buildCategoryTree($child['id']);
        }
        
        return $children;
    }

    /**
     * Create new category
     */
    public function createCategory($data)
    {
        $data['is_active'] = $data['is_active'] ?? true;
        
        if ($this->insert($data)) {
            return $this->getInsertID();
        }
        
        return false;
    }    /**
     * Update category
     */
    public function updateCategory($id, $data)
    {
        // CodeIgniter 4's update() already handles validation with {id} placeholder
        // We just need to ensure the id is available in the validation context
        $this->validationData = array_merge($data, ['id' => $id]);
        
        $result = $this->update($id, $data);
        
        // Clear validation data after use
        $this->validationData = [];
        
        return $result;
    }

    /**
     * Deactivate category
     */
    public function deactivateCategory($id)
    {
        return $this->update($id, ['is_active' => false]);
    }

    /**
     * Activate category
     */
    public function activateCategory($id)
    {
        return $this->update($id, ['is_active' => true]);
    }

    /**
     * Check if category has materials
     */
    public function hasMaterials($categoryId)
    {
        $materialModel = new MaterialModel();
        return $materialModel->where('category_id', $categoryId)->countAllResults() > 0;
    }

    /**
     * Get category statistics
     */
    public function getCategoryStats()
    {
        $stats = [];
        
        $stats['total_categories'] = $this->countAll();
        $stats['active_categories'] = $this->where('is_active', true)->countAllResults();
        $stats['root_categories'] = $this->where('parent_id IS NULL')
                                       ->where('is_active', true)
                                       ->countAllResults();
        
        return $stats;
    }
}
