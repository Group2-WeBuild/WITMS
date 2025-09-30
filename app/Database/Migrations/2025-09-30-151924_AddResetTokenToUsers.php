<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResetTokenToUsers extends Migration
{
    public function up()
    {
        // Add reset token fields to users table
        $fields = [
            'reset_token' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Password reset token'
            ],
            'reset_token_expires' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Reset token expiration timestamp'
            ]
        ];

        $this->forge->addColumn('users', $fields);

        // Add index for reset token for faster lookups
        $this->forge->addKey('reset_token', false, false, 'idx_users_reset_token');
    }

    public function down()
    {
        // Remove the reset token fields
        $this->forge->dropColumn('users', ['reset_token', 'reset_token_expires']);
    }
}
