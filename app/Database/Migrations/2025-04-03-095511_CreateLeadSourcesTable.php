<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeadSourcesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'leadSourceId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'leadSourceName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'description' => [
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

        $this->forge->addKey('leadSourceId', true);
        $this->forge->createTable('lead_sources');
    }

    public function down()
    {
        $this->forge->dropTable('lead_sources');
    }
}
