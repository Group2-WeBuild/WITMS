<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitsOfMeasureModel extends Model
{
    protected $table            = 'units_of_measure';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'abbreviation',
        'is_active'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;    protected array $casts = [
        'id' => 'integer',
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
            'label' => 'Unit Name',
            'rules' => 'required|max_length[50]'
        ],
        'abbreviation' => [
            'label' => 'Abbreviation',
            'rules' => 'required|max_length[10]|is_unique[units_of_measure.abbreviation,id,{id}]'
        ],
        'is_active' => [
            'label' => 'Is Active',
            'rules' => 'permit_empty|in_list[0,1]'
        ]
    ];

    protected $validationMessages = [
        'abbreviation' => [
            'is_unique' => 'This abbreviation already exists in the system.'
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
     * Get all active units
     */
    public function getActiveUnits()
    {
        return $this->where('is_active', true)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Create new unit
     */
    public function createUnit($data)
    {
        $data['is_active'] = $data['is_active'] ?? true;
        
        if ($this->insert($data)) {
            return $this->getInsertID();
        }
        
        return false;
    }    /**
     * Update unit
     */
    public function updateUnit($id, $data)
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
     * Deactivate unit
     */
    public function deactivateUnit($id)
    {
        return $this->update($id, ['is_active' => false]);
    }

    /**
     * Activate unit
     */
    public function activateUnit($id)
    {
        return $this->update($id, ['is_active' => true]);
    }

    /**
     * Check if unit is being used by materials
     */
    public function isUsedByMaterials($unitId)
    {
        $materialModel = new MaterialModel();
        return $materialModel->where('unit_id', $unitId)->countAllResults() > 0;
    }

    /**
     * Get unit statistics
     */
    public function getUnitStats()
    {
        $stats = [];
        
        $stats['total_units'] = $this->countAll();
        $stats['active_units'] = $this->where('is_active', true)->countAllResults();
        $stats['inactive_units'] = $this->where('is_active', false)->countAllResults();
        
        return $stats;
    }
}
