<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePoDetailsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'poDetailId' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'poId' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'itemId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'itemCode' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'item' => [
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
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
            'rate' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
            'amount' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
                'null'       => false,
            ],
            'createdBy' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => false,
            ],
            'createdDate' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'modifiedBy' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => false,
            ],
            'modifiedDate' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
                'on_update' => 'CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addKey('poDetailId', true);
        $this->forge->createTable('po_details');
    }

    public function down()
    {
        $this->forge->dropTable('po_details');
    }
}
