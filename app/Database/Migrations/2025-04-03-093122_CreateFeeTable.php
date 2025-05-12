<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFeeTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'feeId' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'perticularName' => [
                'type'       => 'TEXT',
                'null'       => false,
            ],
            'feesType' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'amount' => [
                'type'       => 'DOUBLE',
                'null'       => false,
                'default'    => 0,
            ],
            'isActive' => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1, // Default: Active
            ],
            'isDeleted' => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0, // Default: Not deleted
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
    
        $this->forge->addKey('feeId', true); 
        $this->forge->createTable('fee_mst'); 
    }
    
    public function down()
    {
        $this->forge->dropTable('fee_mst');
    }
    
}
