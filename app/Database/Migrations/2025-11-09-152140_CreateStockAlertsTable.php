<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockAlertsTable extends Migration
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
            'material_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'warehouse_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'alert_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Low Stock', 'Out of Stock', 'Expiring Soon', 'Expired'],
                'null'       => false,
            ],
            'current_quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
            ],
            'threshold_quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'is_resolved' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'resolved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'resolved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey(['material_id', 'warehouse_id'], false);
        $this->forge->addKey(['is_resolved'], false);
        $this->forge->addForeignKey('material_id', 'materials', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('resolved_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('stock_alerts');
    }

    public function down()
    {
        $this->forge->dropTable('stock_alerts');
    }
}
