<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\TenantService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Database\Config;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\I18n\Time;
use App\Models\TenantModel;
use Config\Database;

class Tenant extends BaseController
{

    use ResponseTrait;

    public function create()
    {
        $input = $this->request->getJSON();
        $rules = [
            'tenantName' => 'required|min_length[6]|max_length[6]',
            'databaseName' => 'required',
            'username' => 'required',
            'hostUrl' => 'required',
        ];
        if (!$this->validate($rules)) {
            return $this->fail(['status' => false, 'errors' => $this->validator->getErrors(), 'message' => 'Invalid Inputs'], 409);
        }
        
        // Set the default database connection (or use the connection group)
        $model = new TenantModel();
        $tenant = $model->where('tenantName', $input->tenantName)->orWhere('databaseName', $input->databaseName)->first();
        if ($tenant) {
            return $this->respond([
                'status' => false,
                'message' => 'Tenant already exists',
            ], 200);
        }

        // Set the default database connection (or use the connection group)
        $db = Database::connect();
        // Check if the database connection is successful
        if (!$db) {
            return $this->respond([
                'status' => false,
                'message' => 'Database connection failed',
            ], 500);
        }

        // Check if the tenant database exists
        if ($this->databaseExists($db, $input->databaseName)) {
            return $this->respond([
                'status' => false,
                'message' => 'Tenant database already exists',
            ], 200);
        }
        // Create the tenant database
        $databaseCreated = $this->createDatabase($db, $input->databaseName);
        
        // Insert the tenant information into the database
        if($model->insert($input)){
             // Fetch tenant's database configuration
             $tenantConfig = [
                'DSN'      => '',
                'hostname' => $input->hostUrl,
                'username' => $input->username,
                'password' => $input->password,
                'database' => $input->databaseName,
                'DBDriver' => 'MySQLi',
                'DBPrefix' => '',
                'pConnect' => false,
                'DBDebug'  => true,
                'charset'  => 'utf8',
                'DBCollat' => 'utf8_general_ci',
                'swapPre'  => '',
                'encrypt'  => false,
                'compress' => false,
                'strictOn' => false,
                'failover' => [],
                'port'    => 3306,
            ];

            // Create a new database connection for the tenant
            $tenantDb = Database::connect($tenantConfig);

            // Now, run the migration to create tenant-specific tables
            if($databaseCreated){
                $this->runTenantMigration($tenantDb);
                return $this->respond([
                    'status' => true,
                    'message' => 'Tenant created successfully',
                    'data' => [
                        'tenantName' => $input->tenantName,
                        'databaseName' => $input->databaseName,
                        'username' => $input->username,
                        'hostUrl' => $input->hostUrl,
                    ],
                ], 200);  
            }else{
                return $this->respond([
                    'status' => false,
                    'message' => 'Failed to create tenant database',
                ], 500);
            }
            
        }else{
            return $this->respond([
                'status' => false,
                'message' => 'Failed to create tenant',
            ], 500);
        }

                
    }

    public function generateTenantDatabase()
    {
        $input = $this->request->getJSON();
        $rules = [
            'tenantName' => 'required|min_length[6]|max_length[6]',
        ];
        if (!$this->validate($rules)) {
            return $this->fail(['status' => false, 'errors' => $this->validator->getErrors(), 'message' => 'Invalid Inputs'], 409);
        }
        
        // Set the default database connection (or use the connection group)
        $model = new TenantModel();
        $tenant = $model->where('tenantName', $input->tenantName)->where('isDeleted',0)->first();
        
        // Set the default database connection (or use the connection group)
        $db = Database::connect();
        // Check if the database connection is successful
        if (!$db) {
            return $this->respond([
                'status' => false,
                'message' => 'Database connection failed',
            ], 500);
        }

        // Check if the tenant database exists
        if ($this->databaseExists($db, $tenant['databaseName'])) {
            return $this->respond([
                'status' => false,
                'message' => 'Tenant database already exists',
            ], 200);
        }
        // Create the tenant database
        $databaseCreated = $this->createDatabase($db, $tenant['databaseName']);
        
         // Fetch tenant's database configuration
         $tenantDbConfig = [
            'DSN'      => '',
            'hostname' => $tenant['hostUrl'],  // Adjust to your database host
            'username' => $tenant['username'],       // Adjust to your database username
            'password' => '',           // Adjust to your database password
            'database' => $tenant['databaseName'],  // The tenant-specific database
            'DBDriver' => 'MySQLi',     // Adjust based on your DBMS
        ];

        // Create a new database connection for the tenant
        $tenantDb = Database::connect($tenantDbConfig);

        // Now, run the migration to create tenant-specific tables
        if($databaseCreated){
            $this->runTenantMigration($tenantDb);
            return $this->respond([
                'status' => true,
                'message' => 'Tenant created successfully',
                'data' => [],
            ], 200);  
        }else{
            return $this->respond([
                'status' => false,
                'message' => 'Failed to create tenant database',
            ], 500);
        }

                
    }

    /**
     * Check if the tenant database exists.
     *
     * @param ConnectionInterface $db
     * @param string $tenantName
     * @return bool
     */
    private function databaseExists(ConnectionInterface $db, $tenantName)
    {
        // Query the system to check if the database exists
        $query = $db->query("SHOW DATABASES LIKE '$tenantName'");
        return $query->getRowArray() !== null;
    }

    /**
     * Create a new tenant database.
     *
     * @param ConnectionInterface $db
     * @param string $tenantName
     * @return void
     */
    private function createDatabase(ConnectionInterface $db, $tenantName)
    {
        // Create the database if it does not exist
        $db->query("CREATE DATABASE `$tenantName`");
    }

    /**
     * Run the migration for the tenant's database.
     *
     * @param ConnectionInterface $tenantDb
     * @return void
     */
    private function runTenantMigration(ConnectionInterface $tenantDb)
    {
        // Load migration class to apply the migrations for tenant tables
        $migrator = \Config\Services::migrator();

        // Set the database connection for migrations
        $migrator->setDatabase($tenantDb);

        // Run the migration for tenant tables
        $migrator->latest();
    }
}
