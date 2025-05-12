<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeadInterestedTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'leadInterestedId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'leadInterestedName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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

        $this->forge->addKey('leadInterestedId', true);
        $this->forge->createTable('lead_interested');
    }

    public function down()
    {
        $this->forge->dropTable('lead_interested');
    }
}
