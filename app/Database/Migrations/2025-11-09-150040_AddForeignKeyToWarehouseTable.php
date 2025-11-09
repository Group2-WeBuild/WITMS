<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddForeignKeyToWarehouseTable extends Migration
{
    public function up()
    {
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
        $this->db->query('
            ALTER TABLE warehouses 
            DROP FOREIGN KEY warehouses_manager_fk
        ');
    }
}
