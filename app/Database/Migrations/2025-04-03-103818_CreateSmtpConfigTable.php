<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSmtpConfigTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'smtpId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'protocol' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'smtpHost' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'smtpPort' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'fromMail' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'smtpUser' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'smtpPass' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'updUserId' => [
                'type'       => 'INT',
                'constraint' => 25,
                'unsigned'   => true,
                'null'       => false,
            ],
            'updDatetime' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'isActive' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'isDeleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
        ]);

        $this->forge->addKey('smtpId', true);
        $this->forge->createTable('smtp_config_mst');
    }

    public function down()
    {
        $this->forge->dropTable('smtp_config_mst');
    }
}
