<?php

namespace App\Models;

use CodeIgniter\Model;

class WorkAssignmentModel extends Model
{
    protected $table = 'work_assignments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'warehouse_id',
        'task_type',
        'task_description',
        'location',
        'priority',
        'deadline',
        'status',
        'assigned_by'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAssignmentsByUser($userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('deadline', 'ASC')
                    ->findAll();
    }

    public function getActiveAssignments()
    {
        return $this->whereIn('status', ['pending', 'in_progress'])
                    ->orderBy('deadline', 'ASC')
                    ->findAll();
    }

    public function getAssignmentWithDetails($id)
    {
        $this->select('work_assignments.*, u1.first_name, u1.last_name as staff_name, u2.first_name as manager_first, u2.last_name as manager_last')
             ->join('users u1', 'work_assignments.user_id = u1.id')
             ->join('users u2', 'work_assignments.assigned_by = u2.id')
             ->where('work_assignments.id', $id);
        
        return $this->first();
    }

    public function updateStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }
}
