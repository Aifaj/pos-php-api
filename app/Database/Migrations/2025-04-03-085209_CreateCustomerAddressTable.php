<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerAddressTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'customerAddressId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'customerId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'fullName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'mobileNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'pincode' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'addressLine1' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'addressLine2' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'landmark' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'state' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'country' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'deliveryInstruction' => [
                'type' => 'TEXT',
                'null' => true,
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
            'createdDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'createdBy' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => true,
            ],
            'modifiedDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'modifiedBy' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => true,
            ],
            
        ]);
    
        $this->forge->addKey('customerAddressId', true); 
        $this->forge->createTable('customer_address'); 
    }
    
    public function down()
    {
        $this->forge->dropTable('customer_address');
    }
    
}
