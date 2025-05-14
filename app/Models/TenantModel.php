<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantModel extends Model
{
    protected $table            = 'tenant_mst';
    protected $primaryKey       = 'tenantId';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['tenantId', 'tenantName', 'databaseName', 'username', 'password', 'hostUrl', 'createdBy', 'createdDate', 'modifiedBy', 'modifiedDate', 'isActive', 'isDeleted'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'createdDate';
    protected $updatedField  = 'modifiedDate';
    protected $deletedField  = 'deletedDate';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get tenant database configuration.
     *
     * @param string $tenantName
     * @return array|null
     */
    public function getTenantDatabaseConfig(string $tenantName): ?array
    {
        $tenant = $this->where('tenantName', $tenantName)
                       ->where('isActive', 1) // Only fetch active tenants
                       ->where('isDeleted', 0)
                       ->first();

        if (!$tenant) {
            return null;
        }

        // return $tenantConfig = [
        //     'DBDriver' => 'MySQLi',
        //     'hostname' => 'localhost', // or the host where your tenant's database is located
        //     'username' => $tenant['username'],
        //     'password' => $tenant['password'],
        //     'database' => $tenant['databaseName'],
        //     'DBDebug'  => true,  // Enable for debugging, to get detailed error messages
        //     'charset'  => 'utf8',
        //     'DBCollat' => 'utf8_general_ci',
        // ];

        return $tenantConfig = [
            'DSN'      => '',
            'hostname' => 'localhost',
            'username' => $tenant['username'],
            'password' => $tenant['password'],
            'database' => $tenant['databaseName'],
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
    }

}
