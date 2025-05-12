<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudentTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'studentId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'studentCode' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'generalRegisterNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'firstName' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'middleName' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'lastName' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'motherName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'gender' => [
                'type'       => 'VARCHAR',
                'constraint' => 11,
                'null'       => false,
            ],
            'birthDate' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'birthPlace' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'nationality' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Indian',
                'null'       => false,
            ],
            'religion' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'cast' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'subCast' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'motherTongue' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'bloodGroup' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'aadharNo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'medium' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'physicallyHandicapped' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'educationalGap' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'profilePic' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'registeredDate' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'isLeft' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
        ]);

        $this->forge->addKey('studentId', true);
        $this->forge->createTable('student_mst');
    }

    public function down()
    {
        $this->forge->dropTable('student_mst');
    }
}
