<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCourseTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'courseId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'courseName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'courseDesc' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'price' => [
                'type'  => 'DOUBLE',
                'null'  => true,
            ],
            'discount' => [
                'type'  => 'DOUBLE',
                'null'  => true,
            ],
            'startDate' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'duration' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'finalPrice' => [
                'type'  => 'DOUBLE',
                'null'  => true,
            ],
            'coverImage' => [
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
    
        $this->forge->addKey('courseId', true);
        $this->forge->createTable('course_msts'); 
    }
    
    public function down()
    {
        $this->forge->dropTable('course_mst');
    }
    
}
