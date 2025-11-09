<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccountsReceivableTable extends Migration
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
            'invoice_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'invoice_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
            ],
            'received_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'default'    => '0.00',
            ],
            'balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Pending', 'Partially Paid', 'Paid', 'Overdue', 'Cancelled'],
                'default'    => 'Pending',
            ],
            'issued_by' => [
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
        $this->forge->addUniqueKey(['invoice_number']);
        $this->forge->addKey(['client_id'], false);
        $this->forge->addKey(['issued_by'], false);
        $this->forge->addForeignKey('client_id', 'clients', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('issued_by', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('accounts_receivable');
    }

    public function down()
    {
        $this->forge->dropTable('accounts_receivable');
    }
}
