<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TenantModel;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Exception;
use Throwable;

class TenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $token = null;
        $tenant = null;
        $key = "Exiaa@11";
        $header = $request->getHeader("Authorization");
        if(!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        if($request->getHeaderLine('Tenant')) {
            $tenant = $request->getHeaderLine('Tenant');
        }else {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $tenant = $decoded->tenantName;
        }
        if (empty($tenant)) {
            return service('response')
                ->setStatusCode(400)
                ->setJSON([
                    'status'  => false,
                    'message' => 'Tenant header is required.',
                ]);
        }

        $tenantModel = new TenantModel();

        // Fetch tenant's database configuration
        $tenantConfig = $tenantModel->getTenantDatabaseConfig($tenant);
        
        if (!$tenantConfig) {
            return service('response')
                ->setStatusCode(404)
                ->setJSON([
                    'status'  => false,
                    'tenant' => $tenant,                    
                    'message' => 'Tenant database configuration not found or inactive.',
                ]);
        }

         // Pass tenantConfig to the request headers or make it globally available
         $request->setHeader('X-Tenant-Config', json_encode($tenantConfig));

        // Dynamically connect to the tenant's database
        //Database::connect($tenantConfig, true); // Force a fresh connection
        // try {
        //     // Dynamically connect to the tenant's database
        //     log_message('error', 'Tenant Config: ' . print_r($tenantConfig, true));
           
        //     $tenantDB = \Config\Database::connect($tenantConfig, false);
            
        //     if (!$tenantDB->connID) {
        //         throw new \Exception('Database connection failed.');
        //     }
        // } catch (\Throwable $e) {
        //     log_message('error', 'TenantFilter Database Error: ' . $e->getMessage());
        //     return service('response')
        //         ->setStatusCode(500)
        //         ->setJSON([
        //             'status'  => false,
        //             'message' => 'Database connection error.',
        //             'error'   => $e->getMessage(),
        //         ]);
        // }
        
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing required
    }
}