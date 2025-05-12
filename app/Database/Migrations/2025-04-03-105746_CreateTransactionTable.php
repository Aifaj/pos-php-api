<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'transactionId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'transactionFor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true, // Member or Donation
            ],
            'transactionNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'memberId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'paymentMode' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'amount' => [
                'type'       => 'DOUBLE',
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'razorpayNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'transactionDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'isActive' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'isDeleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'createdBy' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => true,
            ],
            'createdDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'modifiedBy' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'modifiedDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('transactionId', true);
        $this->forge->createTable('transaction_mst');
    }

    public function down()
    {
        $this->forge->dropTable('transaction_mst');
    }
}
