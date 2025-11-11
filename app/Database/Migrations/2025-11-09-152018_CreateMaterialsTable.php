<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaterialsTable extends Migration
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
                'constraint' => 200,
                'null'       => false,
                'comment'    => 'Material name',
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Unique material code',
            ],
            'qrcode' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'QR code',
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Foreign key to material_categories',
            ],
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Foreign key to units_of_measure',
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Material description',
            ],
            'reorder_level' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'default'    => '0.00',
                'comment'    => 'Minimum stock level before reorder',
            ],
            'reorder_quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'default'    => '0.00',
                'comment'    => 'Quantity to order when restocking',
            ],
            'unit_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'default'    => '0.00',
                'comment'    => 'Standard unit cost',
            ],
            'is_perishable' => [
                'type'    => 'BOOLEAN',
                'default' => false,
                'comment' => 'Whether material has expiration date',
            ],
            'shelf_life_days' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Shelf life in days if perishable',
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addUniqueKey(['code']);
        $this->forge->addUniqueKey(['qrcode']);
        $this->forge->addKey(['category_id'], false);
        $this->forge->addKey(['unit_id'], false);
        $this->forge->addForeignKey('category_id', 'material_categories', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('unit_id', 'units_of_measure', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('materials');
    }

    public function down()
    {
        $this->forge->dropTable('materials');
    }
}
