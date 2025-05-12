<?php

namespace App\Libraries;

use Config\Database;
use Config\Services;

class TenantService
{
    public function getTenantConfig($tenantConfigHeader)
    {
        if (!$tenantConfigHeader) {
            throw new \Exception('Tenant configuration not found.');
        }

        // Decode the tenantConfig JSON
        $tenantConfig = json_decode($tenantConfigHeader, true);

        if (!$tenantConfig) {
            throw new \Exception('Invalid tenant configuration.');
        }

        // Connect to the tenant's database
        return Database::connect($tenantConfig);
    }
}
