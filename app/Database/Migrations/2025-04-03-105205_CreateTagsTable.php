<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTagsTable extends Migration
{
    
    public function up()
    {
        $this->forge->addField([
            'tagId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tagName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'isActive' => [
                'type'       => 'INT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'isDeleted' => [
                'type'       => 'INT',
                'constraint' => 1,
                'default'    => 0,
            ],
        ]);

        $this->forge->addKey('tagId', true);
        $this->forge->createTable('tags_mst');
    }

    public function down()
    {
        $this->forge->dropTable('tags_mst');
    }
}
