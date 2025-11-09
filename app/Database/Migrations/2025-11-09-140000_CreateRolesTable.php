<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolesTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Role name',
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Role description',
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
                'comment' => 'Whether the role is active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['name']);
        $this->forge->createTable('roles');

        // Insert default roles
        $roles = [
            ['name' => 'Warehouse Manager', 'description' => 'Manages warehouse operations', 'is_active' => true],
            ['name' => 'Warehouse Staff', 'description' => 'Performs warehouse tasks', 'is_active' => true],
            ['name' => 'Inventory Auditor', 'description' => 'Audits inventory records', 'is_active' => true],
            ['name' => 'Procurement Officer', 'description' => 'Handles procurement processes', 'is_active' => true],
            ['name' => 'Accounts Payable Clerk', 'description' => 'Manages accounts payable', 'is_active' => true],
            ['name' => 'Accounts Receivable Clerk', 'description' => 'Manages accounts receivable', 'is_active' => true],
            ['name' => 'IT Administrator', 'description' => 'Manages IT systems and infrastructure', 'is_active' => true],
            ['name' => 'Top Management', 'description' => 'Executive level management', 'is_active' => true],
        ];

        $builder = $this->db->table('roles');
        foreach ($roles as $role) {
            $builder->insert($role);
        }
    }

    public function down()
    {
        $this->forge->dropTable('roles');
    }
}
