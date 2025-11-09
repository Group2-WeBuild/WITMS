<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryAuditDetailsTable extends Migration
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
            'audit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'material_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'batch_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'system_quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'comment'    => 'Quantity per system records',
            ],
            'physical_quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'comment'    => 'Actual counted quantity',
            ],
            'variance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'comment'    => 'Difference between physical and system',
            ],
            'variance_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
                'comment'    => 'Monetary value of variance',
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey(['audit_id'], false);
        $this->forge->addKey(['material_id'], false);
        $this->forge->addForeignKey('audit_id', 'inventory_audits', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('material_id', 'materials', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('inventory_audit_details');
    }

    public function down()
    {
        $this->forge->dropTable('inventory_audit_details');
    }
}
