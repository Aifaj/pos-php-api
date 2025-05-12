<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItemFeeMapTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'itemFeeMapId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'itemId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'unsigned'   => true,
                'null'       => false,
            ],
            'feeId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'unsigned'   => true,
                'null'       => false,
            ],
            'isDeleted' => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0, 
            ],
           
        ]);
    
        $this->forge->addKey('itemFeeMapId', true); 
        $this->forge->createTable('item_fee_map'); 
    }
    
    public function down()
    {
        $this->forge->dropTable('item_fee_map');
    }
    
}
