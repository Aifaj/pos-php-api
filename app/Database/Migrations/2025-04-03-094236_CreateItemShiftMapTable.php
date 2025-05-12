<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItemShiftMapTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'itemShiftMapId' => [
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
            'shiftId' => [
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

        $this->forge->addKey('itemShiftMapId', true);
        $this->forge->createTable('item_shift_map');
    }

    public function down()
    {
        $this->forge->dropTable('item_shift_map');
    }
}
