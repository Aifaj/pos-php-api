<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentDetailsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'paymentId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'admissionId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'paidAmount' => [
                'type' => 'DOUBLE',
                'null' => false,
            ],
            'paymentMode' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'transactionNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'paymentDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'dueDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'paymentReceiptNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'isActive' => [
                'type'       => 'INT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
            ],
            'isDeleted' => [
                'type'       => 'INT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
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
                'constraint' => 25,
                'null'       => true,
            ],
            'modifiedDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('paymentId', true);
        $this->forge->createTable('payment_details');
    }

    public function down()
    {
        $this->forge->dropTable('payment_details');
    }
}
