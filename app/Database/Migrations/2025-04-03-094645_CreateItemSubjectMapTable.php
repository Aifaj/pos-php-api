<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItemSubjectMapTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'itemSubjectMapId' => [
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
            'subjectId' => [
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

        $this->forge->addKey('itemSubjectMapId', true);
        $this->forge->createTable('item_subject_map');
    }

    public function down()
    {
        $this->forge->dropTable('item_subject_map');
    }
}
