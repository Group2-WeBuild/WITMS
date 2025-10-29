<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description', 'is_active'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[roles.name,id,{id}]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Role name is required',
            'is_unique'  => 'Role name already exists',
            'max_length' => 'Role name cannot exceed 100 characters',
        ],
    ];

    protected $skipValidation = false;
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
     * Get all active roles
     */
    public function getActiveRoles()
    {
        return $this->where('is_active', true)->findAll();
    }

    /**
     * Get role by name
     */
    public function getRoleByName(string $name)
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Get role ID by name
     */
    public function getRoleIdByName(string $name): ?int
    {
        $role = $this->getRoleByName($name);
        return $role ? $role['id'] : null;
    }

    /**
     * Check if role exists by name
     */
    public function roleExists(string $name): bool
    {
        return $this->where('name', $name)->countAllResults() > 0;
    }

    /**
     * Get all role names as array
     */
    public function getAllRoleNames(): array
    {
        $roles = $this->select('name')->findAll();
        return array_column($roles, 'name');
    }
}
