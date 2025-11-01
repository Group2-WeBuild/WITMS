<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],              'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'User email address',
            ],'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Hashed password',
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Foreign key to roles table',
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Foreign key to departments table',
            ],            
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'User first name',
            ],
            'middle_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'User middle name',
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'User last name',
            ],
            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'User contact number',
            ],
            'is_active' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
                'comment'    => 'User account status',
            ],
            'last_login' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Last login timestamp',
            ],
            'email_verified_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Email verification timestamp',
            ],            'reset_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Password reset token',
            ],
            'reset_token_expires' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Reset token expiration timestamp',
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
        $this->forge->addUniqueKey(['email']);
        $this->forge->addKey(['role_id'], false);
        $this->forge->addKey(['department_id'], false);
        $this->forge->addKey(['reset_token'], false);
        
        // Add foreign key constraints
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
