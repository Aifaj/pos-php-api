<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItemTypeTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'itemTypeId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'itemTypeName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'description' => [
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
        ]);

        $this->forge->addKey('itemTypeId', true);
        $this->forge->createTable('item_type');
    }

    public function down()
    {
        $this->forge->dropTable('item_type');
    }
}
