<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrderDetailsTable extends Migration
{ public function up()
    {
        $this->forge->addField([
            'orderDetailId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'orderId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'unsigned'   => true,
                'null'       => false,
            ],
            'item' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'itemId' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'itemCode' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'unitName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'quantity' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'rate' => [
                'type'       => 'DOUBLE',
                'null'       => false,
            ],
            'amount' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'totalItem' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'total' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'subtotal' => [
                'type'       => 'DOUBLE',
                'null'       => false,
            ],
            'discount' => [
                'type'       => 'DOUBLE',
                'null'       => false,
            ],
            'taxAmount' => [
                'type'       => 'DOUBLE',
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

        $this->forge->addKey('orderDetailId', true);
        $this->forge->createTable('order_details');
    }

    public function down()
    {
        $this->forge->dropTable('order_details');
    }
}
