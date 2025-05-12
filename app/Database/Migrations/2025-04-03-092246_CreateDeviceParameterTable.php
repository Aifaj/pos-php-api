<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDeviceParameterTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'iotId' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ipAddress' => [
                'type'       => 'VARCHAR',
                'constraint' => 17,
                'null'       => false,
            ],
            'postId' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => false,
            ],
            'deviceId' => [
                'type'       => 'VARCHAR',
                'constraint' => 8,
                'null'       => false,
            ],
            'sensorDatatype' => [
                'type'       => 'INT',
                'constraint' => 25,
                'null'       => false,
            ],
            'localMillis' => [
                'type'       => 'BIGINT',
                'constraint' => 255,
                'null'       => false,
            ],
            'rtcTimestamp' => [
                'type'       => 'BIGINT',
                'constraint' => 255,
                'null'       => false,
            ],
            'deviceTemperature' => [
                'type'       => 'DOUBLE',
                'null'       => false,
            ],
            'imuPitch' => [
                'type'       => 'DOUBLE',
                'null'       => false,
            ],
            'imuRoll' => [
                'type'       => 'DOUBLE',
                'null'       => false,
            ],
            'imuYaw' => [
                'type'       => 'DOUBLE',
                'null'       => false,
            ],
            'ambianceHumidity' => [
                'type'       => 'DOUBLE',
                'null'       => false,
            ],
            'ambianceTemperature' => [
                'type'       => 'DOUBLE',
                'null'       => false,
            ],
            'heatIndex' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'surfaceTemperature' => [
                'type'       => 'DOUBLE',
                'null'       => false,
            ],
            'deepSoilMoisture' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'strainMeasurement' => [
                'type'       => 'DOUBLE',
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
                'default'    => 1, // Default: Active
            ],
            'isDeleted' => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0, 
            ],
           
        ]);
    
        $this->forge->addKey('iotId', true); 
        $this->forge->createTable('device_parameter'); 
    }
    
    public function down()
    {
        $this->forge->dropTable('device_parameter');
    }
    
}
