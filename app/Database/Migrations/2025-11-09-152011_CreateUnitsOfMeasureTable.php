<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnitsOfMeasureTable extends Migration
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
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Unit name (e.g., pieces, meters, liters)',
            ],
            'abbreviation' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'Unit abbreviation (e.g., pcs, m, L)',
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
        $this->forge->addUniqueKey(['abbreviation']);
        $this->forge->createTable('units_of_measure');
    }

    public function down()
    {
        $this->forge->dropTable('units_of_measure');
    }
}
