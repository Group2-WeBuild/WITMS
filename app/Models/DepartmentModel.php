<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table            = 'departments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
      protected $allowedFields    = [
        'name',
        'description',
        'warehouse_id',
        'department_head',
        'contact_email',
        'contact_phone',
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
    protected $deletedField  = 'deleted_at';    // Validation
    protected $validationRules = [
        'name' => [
            'label' => 'Department Name',
            'rules' => 'required|max_length[100]|is_unique[departments.name,id,{id}]'
        ],
        'description' => [
            'label' => 'Description',
            'rules' => 'permit_empty|max_length[500]'
        ],
        'warehouse_id' => [
            'label' => 'Warehouse',
            'rules' => 'permit_empty|is_natural_no_zero|is_not_unique[warehouses.id]'
        ],
        'department_head' => [
            'label' => 'Department Head',
            'rules' => 'permit_empty|max_length[200]|alpha_space'
        ],
        'contact_email' => [
            'label' => 'Contact Email',
            'rules' => 'permit_empty|valid_email|max_length[255]'
        ],
        'contact_phone' => [
            'label' => 'Contact Phone',
            'rules' => 'permit_empty|max_length[20]|regex_match[/^[\+]?[0-9\s\-\(\)]+$/]'
        ],
        'is_active' => [
            'label' => 'Active Status',
            'rules' => 'permit_empty|in_list[0,1]'
        ]
    ];
    
    protected $validationMessages = [
        'name' => [
            'is_unique' => 'This department name already exists in WeBuild system.'
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
     * Get active departments only
     */
    public function getActiveDepartments()
    {
        return $this->where('is_active', 1)->findAll();
    }    /**
     * Get departments by warehouse ID
     */
    public function getDepartmentsByWarehouse(int $warehouseId)
    {
        return $this->where('warehouse_id', $warehouseId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get warehouse departments (excluding Central Office)
     */
    public function getWarehouseDepartments()
    {
        return $this->where('warehouse_id IS NOT NULL')
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get central office departments
     */
    public function getCentralOfficeDepartments()
    {
        return $this->where('warehouse_id IS NULL')
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Find department by name
     */
    public function findByName(string $name)
    {
        return $this->where('name', $name)->first();
    }    /**
     * Get departments with user count
     */
    public function getDepartmentsWithUserCount()
    {
        return $this->select('departments.*, warehouses.name as warehouse_name, COUNT(users.id) as user_count')
                    ->join('warehouses', 'warehouses.id = departments.warehouse_id', 'left')
                    ->join('users', 'users.department_id = departments.id', 'left')
                    ->where('departments.is_active', 1)
                    ->groupBy('departments.id')
                    ->findAll();
    }

    /**
     * Activate/Deactivate department
     */
    public function toggleDepartmentStatus(int $departmentId, bool $status): bool
    {
        return $this->update($departmentId, ['is_active' => $status]);
    }

    /**
     * Create new department
     */
    public function createDepartment(array $departmentData): bool
    {
        // Set default values
        $departmentData['is_active'] = $departmentData['is_active'] ?? 1;
        
        return $this->insert($departmentData);
    }

    /**
     * Update department information
     */
    public function updateDepartment(int $departmentId, array $departmentData): bool
    {
        return $this->update($departmentId, $departmentData);
    }    /**
     * Get department statistics
     */
    public function getDepartmentStats(): array
    {
        $stats = [
            'total_departments' => $this->countAll(),
            'active_departments' => $this->where('is_active', 1)->countAllResults(false),
            'inactive_departments' => $this->where('is_active', 0)->countAllResults(false),
            'central_office_departments' => $this->where('warehouse_id IS NULL')
                                                 ->where('is_active', 1)
                                                 ->countAllResults(false),
            'warehouse_departments' => $this->where('warehouse_id IS NOT NULL')
                                            ->where('is_active', 1)
                                            ->countAllResults(false),
        ];

        // Get distribution by warehouse
        $warehouseModel = model('WarehouseModel');
        $warehouses = $warehouseModel->where('is_active', 1)->findAll();
        
        $stats['warehouse_distribution'] = [];
        foreach ($warehouses as $warehouse) {
            $stats['warehouse_distribution'][$warehouse['id']] = [
                'warehouse_name' => $warehouse['name'],
                'department_count' => $this->where('warehouse_id', $warehouse['id'])
                                          ->where('is_active', 1)
                                          ->countAllResults(false)
            ];
        }

        return $stats;
    }

    /**
     * Search departments by name or description
     */
    public function searchDepartments(string $keyword)
    {
        return $this->groupStart()
                    ->like('name', $keyword)
                    ->orLike('description', $keyword)
                    ->orLike('department_head', $keyword)
                    ->groupEnd()
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get departments that need managers (no department head)
     */
    public function getDepartmentsNeedingManagers()
    {
        return $this->groupStart()
                    ->where('department_head IS NULL')
                    ->orWhere('department_head', '')
                    ->groupEnd()
                    ->where('is_active', 1)
                    ->findAll();
    }

    /**
     * Get department with warehouse details
     */
    public function getDepartmentWithWarehouse(int $departmentId)
    {
        return $this->select('departments.*, warehouses.name as warehouse_name, warehouses.code as warehouse_code, warehouses.location as warehouse_location')
                    ->join('warehouses', 'warehouses.id = departments.warehouse_id', 'left')
                    ->where('departments.id', $departmentId)
                    ->first();
    }

    /**
     * Get all departments with warehouse details
     */
    public function getAllDepartmentsWithWarehouse()
    {
        return $this->select('departments.*, warehouses.name as warehouse_name, warehouses.code as warehouse_code')
                    ->join('warehouses', 'warehouses.id = departments.warehouse_id', 'left')
                    ->where('departments.is_active', 1)
                    ->findAll();
    }

    /**
     * Assign department to warehouse
     */
    public function assignToWarehouse(int $departmentId, ?int $warehouseId): bool
    {
        return $this->update($departmentId, ['warehouse_id' => $warehouseId]);
    }

    /**
     * Remove department from warehouse (assign to Central Office)
     */
    public function removeFromWarehouse(int $departmentId): bool
    {
        return $this->update($departmentId, ['warehouse_id' => null]);
    }

    /**
     * Check if department belongs to warehouse
     */
    public function belongsToWarehouse(int $departmentId, int $warehouseId): bool
    {
        $department = $this->find($departmentId);
        return $department && $department['warehouse_id'] == $warehouseId;
    }

    /**
     * Get departments count by warehouse
     */
    public function getDepartmentCountByWarehouse(int $warehouseId): int
    {
        return $this->where('warehouse_id', $warehouseId)
                    ->where('is_active', 1)
                    ->countAllResults();
    }
}
