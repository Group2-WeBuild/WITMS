<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserWarehouseAssignmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Foreign key to users table',
            ],
            'warehouse_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Foreign key to warehouses table',
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Optional: Specific role for this warehouse assignment',
            ],
            'is_primary' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'True if this is the primary warehouse for this user',
            ],
            'assigned_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User who assigned this warehouse (usually Warehouse Manager or IT Admin)',
            ],
            'assigned_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'Date when assignment was made',
            ],
            'is_active' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
                'null'       => false,
                'comment'    => 'Whether this assignment is currently active',
            ],
            'notes' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Optional notes about this assignment',
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('assigned_by', 'users', 'id', 'SET NULL', 'CASCADE');
        
        // Unique constraint: A user can only be assigned to the same warehouse once
        $this->forge->addUniqueKey(['user_id', 'warehouse_id']);
        
        // Index for faster lookups
        $this->forge->addKey(['user_id', 'is_active']);
        $this->forge->addKey(['warehouse_id', 'is_active']);
        
        $this->forge->createTable('user_warehouse_assignments');
    }

    public function down()
    {
        $this->forge->dropTable('user_warehouse_assignments');
    }
}

