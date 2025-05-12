<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShiftTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'shiftId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'shiftName' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => false,
            ],
            'startTime' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => false,
            ],
            'endTime' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => false,
            ],
            'emailTime' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => false,
            ],
            'createdDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'createdBy' => [
                'type'       => 'INT',
                'constraint' => 25,
                'unsigned'   => true,
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
                'unsigned'   => true,
                'null'       => true,
            ],
            'isDeleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'isActive' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
        ]);

        $this->forge->addKey('shiftId', true);
        $this->forge->createTable('shift_mst');
    }

    public function down()
    {
        $this->forge->dropTable('shift_mst');
    }
}
