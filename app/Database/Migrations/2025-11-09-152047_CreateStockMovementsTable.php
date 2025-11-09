<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockMovementsTable extends Migration
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
            'reference_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Unique movement reference',
            ],
            'material_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'from_warehouse_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Source warehouse (null for new stock)',
            ],
            'to_warehouse_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Destination warehouse (null for stock out)',
            ],
            'movement_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Receipt', 'Transfer', 'Issue', 'Adjustment', 'Return'],
                'null'       => false,
            ],
            'quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
            ],
            'batch_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'movement_date' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'performed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'User who performed the movement',
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User who approved the movement',
            ],
            'reference_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Reference to related record (PO, project, etc.)',
            ],
            'reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Type of reference (purchase_order, project, etc.)',
            ],
            'notes' => [
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
        $this->forge->addUniqueKey(['reference_number']);
        $this->forge->addKey(['material_id'], false);
        $this->forge->addKey(['from_warehouse_id'], false);
        $this->forge->addKey(['to_warehouse_id'], false);
        $this->forge->addKey(['performed_by'], false);
        $this->forge->addForeignKey('material_id', 'materials', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('from_warehouse_id', 'warehouses', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('to_warehouse_id', 'warehouses', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('performed_by', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('stock_movements');
    }

    public function down()
    {
        $this->forge->dropTable('stock_movements');
    }
}
