<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReceiptsTable extends Migration
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
            'receipt_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'accounts_receivable_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'receipt_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
            ],
            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['Cash', 'Check', 'Bank Transfer', 'Credit Card'],
                'null'       => false,
            ],
            'reference_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'processed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
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
        $this->forge->addUniqueKey(['receipt_number']);
        $this->forge->addKey(['accounts_receivable_id'], false);
        $this->forge->addKey(['processed_by'], false);
        
        // foreign keys
        $this->forge->addForeignKey('accounts_receivable_id', 'accounts_receivable', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('processed_by', 'users', 'id', 'RESTRICT', 'CASCADE');
        
        $this->forge->createTable('receipts');
    }

    public function down()
    {
        $this->forge->dropTable('receipts');
    }
}
