<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeDepartmentHeadToUserId extends Migration
{
    public function up()
    {
        // First, add a new column for user_id
        $this->forge->addColumn('departments', [
            'department_head_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'department_head',
                'comment'    => 'Foreign key to users table'
            ]
        ]);
        
        // Add foreign key constraint
        $this->db->query('
            ALTER TABLE departments 
            ADD CONSTRAINT departments_department_head_user_id_foreign 
            FOREIGN KEY (department_head_user_id) 
            REFERENCES users(id) 
            ON DELETE SET NULL 
            ON UPDATE CASCADE
        ');
        
        // Add index for better performance
        $this->forge->addKey(['department_head_user_id'], false);
        
        // Note: We keep the old department_head VARCHAR field for backward compatibility
        // You can drop it later after migrating data if needed
    }

    public function down()
    {
        // Drop foreign key
        $this->db->query('
            ALTER TABLE departments 
            DROP FOREIGN KEY departments_department_head_user_id_foreign
        ');
        
        // Drop the column
        $this->forge->dropColumn('departments', 'department_head_user_id');
    }
}

