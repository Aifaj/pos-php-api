<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeliveryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'deliveryId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'orderNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'orderDetailId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'courierId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'customerId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'deliveryCode' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'trackingNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'deliveryAmount' => [
                'type'       => 'DOUBLE',
                'null'       => true,
            ],
            'shippingAddressId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => true,
            ],
            'shippingPincode' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'isActive' => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1, 
            ],
            'isDeleted' => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0, 
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
           
        ]);
    
        $this->forge->addKey('deliveryId', true); 
        $this->forge->createTable('delivery_mst'); 
    }
    
    public function down()
    {
        $this->forge->dropTable('delivery_mst');
    }
    
}
