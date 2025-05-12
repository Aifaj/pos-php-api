<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExamTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'examId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'examName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'examCode' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'academicYear' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
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
    
        $this->forge->addKey('examId', true); 
        $this->forge->createTable('exam_mst'); 
    }
    
    public function down()
    {
        $this->forge->dropTable('exam_mst');
    }
    
}
