<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVendorTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'vendorId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vendorType' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => false,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => false,
            ],
            'vendorCode' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'gender' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => false,
            ],
            'mobileNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 12,
                'null'       => true,
            ],
            'profilePic' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'alternateMobileNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 12,
                'null'       => true,
            ],
            'dateOfBirth' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => true,
            ],
            'emailId' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'modifiedBy' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => true,
            ],
            'modifiedDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'createdBy' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => true,
            ],
            'createdDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'isActive' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
            ],
            'isDeleted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('vendorId', true);
        $this->forge->createTable('vendor_mst');
    }

    public function down()
    {
        $this->forge->dropTable('vendor_mst');
    }
}
