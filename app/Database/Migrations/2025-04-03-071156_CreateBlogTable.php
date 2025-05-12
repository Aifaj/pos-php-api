<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlogTable extends Migration
{
    
    public function up()
    {
        $this->forge->addField([
            'blogId' => [
                'type'           => 'INT',
                'constraint'     => 25,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'authorName' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            'profilePic' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
    
        $this->forge->addKey('blogId', true); 
        $this->forge->createTable('blog_mst'); 
    }
    
    public function down()
    {
        $this->forge->dropTable('blog_mst');
    }
    
   
}
