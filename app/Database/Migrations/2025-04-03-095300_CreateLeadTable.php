<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeadTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'leadId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'fName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'lName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'primaryMobileNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => false,
            ],
            'secondaryMobileNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => true,
            ],
            'whatsAppNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'leadInterestedId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'leadInterestedIdValue' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'leadSourceId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'leadSourceValue' => [
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
            'createdBy' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'createdDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'modifiedBy' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'modifiedDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('leadId', true);
        $this->forge->createTable('lead_mst');
    }

    public function down()
    {
        $this->forge->dropTable('lead_mst');
    }
}
