<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItemTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'itemId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'itemName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'itemCode' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'coverImage' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'productImages' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'itemTypeId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'categoryInputFieldValues' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'itemCategoryId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => true,
            ],
            'brandName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'unitName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'unitSize' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'mrp' => [
                'type'  => 'DOUBLE',
                'null'  => true,
            ],
            'finalPrice' => [
                'type'  => 'DOUBLE',
                'null'  => true,
            ],
            'feature' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'termsCondition' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'sku' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'startDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'duration' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'gstPercentage' => [
                'type'  => 'DOUBLE',
                'null'  => true,
            ],
            'discountType' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'discount' => [
                'type'  => 'DOUBLE',
                'null'  => true,
            ],
            'barcode' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'hsnCode' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'minStockLevel' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => true,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'tags' => [
                'type' => 'TEXT',
                'null' => true,
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
        ]);
    
        $this->forge->addKey('itemId', true); 
        $this->forge->createTable('item_mst');
    }
    
    public function down()
    {
        $this->forge->dropTable('item_mst');
    }
    
}
