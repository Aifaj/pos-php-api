<?php
namespace App\Libraries;

use Config\Database;

class TenantDatabase
{
   public static function connectTo(array $tenant)
    {
        return \Config\Database::connect([
            'hostname'   => $tenant['hostUrl'],      
            'username'   => $tenant['username'],    
            'password'   => $tenant['password'],    
            'database'   => $tenant['databaseName'],
            'DBDriver'   => 'MySQLi',
            'DBPrefix'   => '',
            'pConnect'   => false,
            'dbDebug'    => (ENVIRONMENT !== 'production'),
            'charset'    => 'utf8mb4',
            'DBCollat'   => 'utf8mb4_general_ci',
            'cacheOn'    => false,
            'encrypt'    => false,
            'compress'   => false,
            'strictOn'   => false,
            'failover'   => [],
            'saveQueries'=> true
        ], false);
    }
}