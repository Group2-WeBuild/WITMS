<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table            = 'suppliers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'code',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'tax_id',
        'payment_terms',
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
            'label' => 'Supplier Name',
            'rules' => 'required|max_length[200]'
        ],
        'code' => [
            'label' => 'Supplier Code',
            'rules' => 'required|max_length[50]|is_unique[suppliers.code,id,{id}]'
        ],
        'contact_person' => [
            'label' => 'Contact Person',
            'rules' => 'permit_empty|max_length[100]'
        ],
        'email' => [
            'label' => 'Email',
            'rules' => 'permit_empty|valid_email|max_length[100]'
        ],
        'phone' => [
            'label' => 'Phone',
            'rules' => 'permit_empty|max_length[20]'
        ]
    ];

    protected $validationMessages = [
        'code' => [
            'is_unique' => 'This supplier code already exists.'
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
     * Get active suppliers
     */
    public function getActiveSuppliers()
    {
        return $this->where('is_active', true)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Create supplier
     */
    public function createSupplier($data)
    {
        $data['is_active'] = $data['is_active'] ?? true;
        
        if ($this->insert($data)) {
            return $this->getInsertID();
        }
        
        return false;
    }    /**
     * Update supplier
     */
    public function updateSupplier($id, $data)
    {

        $this->validationData = array_merge($data, ['id' => $id]);
        
        $result = $this->update($id, $data);
        
        // Clear validation data after use
        $this->validationData = [];
        
        return $result;
    }

    /**
     * Deactivate supplier
     */
    public function deactivateSupplier($id)
    {
        return $this->update($id, ['is_active' => false]);
    }

    /**
     * Activate supplier
     */
    public function activateSupplier($id)
    {
        return $this->update($id, ['is_active' => true]);
    }

    /**
     * Search suppliers
     */
    public function searchSuppliers($searchTerm)
    {
        return $this->groupStart()
                   ->like('name', $searchTerm)
                   ->orLike('code', $searchTerm)
                   ->orLike('contact_person', $searchTerm)
                   ->groupEnd()
                   ->where('is_active', true)
                   ->findAll();
    }
}
