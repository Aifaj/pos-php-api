<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserTypeModel;
use App\Models\Unit;
use Config\Database;
use App\Libraries\TenantService;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use CodeIgniter\RESTful\ResourceController;

class UserType extends ResourceController
{
    public function getUserTypes()
    {
        $model = new UserTypeModel();
        $data = $model->findAll(); 

        if (!empty($data)) {
            return $this->respond(["status" => true, "message" => "User types fetched", "data" => $data], 200);
        } else {
            return $this->respond(["status" => false, "message" => "No user types found"], 404);
        }
    }
}




// {
//     $tenantService = new TenantService();
    
//     // Connect to the tenant's database
//     $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
    
//     $model = new UserTypeModel($db);
//     $userTypes = $model->findAll();

//     return $this->respond([
//         "status" => true, 
//         "message" => "All User Types Fetched", 
//         "data" => $userTypes
//     ], 200);
// }

