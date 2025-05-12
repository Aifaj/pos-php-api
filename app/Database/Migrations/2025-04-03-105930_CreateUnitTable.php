<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnitTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'unitId' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'unitName' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
            'unitFactor' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'comment'    => '1->Multiply, 2->Divide',
            ],
            'isDeleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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

        $this->forge->addKey('unitId', true);
        $this->forge->createTable('unit_mst');
    }

    public function down()
    {
        $this->forge->dropTable('unit_mst');
    }
}
