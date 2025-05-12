<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdmissionDetailTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'admissionId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'studentId' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => false,
            ],
            'academicYearId' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => false,
            ],
            'selectedCourses' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => false,
            ],
            'rollNo' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => true,
            ],
            'rfId' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'admissionDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('admissionId', true);
        $this->forge->createTable('admission_details');
    }

    public function down()
    {
        $this->forge->dropTable('admission_details');
    }
}
