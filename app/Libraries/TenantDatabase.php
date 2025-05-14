<?php
namespace App\Libraries;

use Config\Database;

class TenantDatabase
{
    public static function connectTo(string $dbName)
    {
        // Return new database connection for tenant
        return \Config\Database::connect([
            'hostname'   => 'localhost',
            'username'   => 'root',
            'password'   => '',
            'database'   => $dbName,
            'DBDriver'   => 'MySQLi',
            'DBPrefix'   => '',
            'pConnect'   => false,
            'dbDebug'    => (ENVIRONMENT !== 'production'),
            'charset'    => 'utf8mb4', // ✅ REQUIRED
            'DBCollat'   => 'utf8mb4_general_ci', // ✅ Optional but recommended
            'cacheOn'    => false,
            'encrypt'    => false,
            'compress'   => false,
            'strictOn'   => false,
            'failover'   => [],
            'saveQueries'=> true
        ], false);
    }
}