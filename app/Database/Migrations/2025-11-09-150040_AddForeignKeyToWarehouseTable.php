<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddForeignKeyToWarehouseTable extends Migration
{    public function up()
    {
        // Add foreign key to warehouses table (manager_id references users.id)
        // Note: warehouse_location_id FK is already defined in CreateWarehousesTable migration
        $this->db->query('
            ALTER TABLE warehouses 
            ADD CONSTRAINT warehouses_manager_fk 
            FOREIGN KEY (manager_id) 
            REFERENCES users(id) 
            ON DELETE SET NULL 
            ON UPDATE CASCADE
        ');
    }

    public function down()
    {
        // Drop foreign key from warehouses table
        $this->db->query('
            ALTER TABLE warehouses 
            DROP FOREIGN KEY warehouses_manager_fk
        ');
    }
}
