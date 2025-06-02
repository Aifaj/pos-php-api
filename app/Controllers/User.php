<?php
 
namespace App\Controllers;
 
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\UserBusiness;
use App\Models\RolePermissionModel;
use App\Models\RightModel;
use App\Models\RoleModel;
use App\Models\BusinessModel;
use App\Models\BusinessCategoryModel;
use App\Models\RestaurantDetails;

use App\Libraries\TenantService;
use App\Models\TenantUserModel;


use App\Models\TenantModel;

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
 
class User extends BaseController
{
    use ResponseTrait;
     
    public function index()
    {
        $users = new UserModel;
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $users->findAll()], 200);
    }
    
    public function profile()
    {
        $key = "Exiaa@11";
        $header = $this->request->getHeader("Authorization");
        $token = null;
  
        // extract the token from the header
        if(!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }
        
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $userModel = new UserModel();
        $rolePermissionModel = new RolePermissionModel();
        $rightModel = new RightModel();
        $roleModel = new RoleModel();

        $input = $this->request->getJSON();
        
        $user = $userModel->where('email', $decoded->email)->first();

        $role = $roleModel->where('roleId', $user['roleId'])->first();
        $user['role'] = $role;

        if(is_null($user)) {
            return $this->respond(['status' => false, 'message' => 'User not found.'], 404);
        }
         
        return $this->respond($user, 200);
    }
    

    public function menu()
    {
        $key = "Exiaa@11";
        $header = $this->request->getHeader("Authorization");
        $tenant = $this->request->getHeader('Tenant');
        $token = null;
  
        // extract the token from the header
        if(!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }
        
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $userModel = new UserModel();
        $rolePermissionModel = new RolePermissionModel();
        $rightModel = new RightModel();
        $roleModel = new RoleModel();

        $input = $this->request->getJSON();
        
        $user = $userModel->where('email', $decoded->email)->first();

        $role = $roleModel->where('roleId', $user['roleId'])->first();
        $user['role'] = $role;
        $menu = [];

        if($user['roleId'] == 1) {
            $rights = $rightModel->where('isDeleted',0)->where('isActive',1)->where('parentRightId',0)->findAll();
            foreach($rights as $key => $right){
                $menuItem = [
                    'route' => $right['route'],
                    'name' => $right['route'],
                    'icon' => $right['iconUrl'],
                    'type' => 'sub', // Default to 'sub' in case it has children
                    'children' => [] // Initialize children array
                ];
                $subMenus = $rightModel->where('parentRightId', $right['rightId'])->findAll();
                if (empty($subMenus)) {
                    $menuItem['type'] = 'link'; // If no children, it's a simple link
                } else {
                    foreach ($subMenus as $subMenu) {
                        $menuItem['children'][] = [
                            'route' => $subMenu['route'],
                            'name' => $subMenu['route'],
                            'icon' => $subMenu['iconUrl'],
                            'type' => 'link'
                        ];
                    }
                }
                $menu[] = $menuItem;
            }
        }else{

            if(isset($decoded->businessCategoryId) && !empty($decoded->businessCategoryId)){
                $rolePermissions = $rolePermissionModel->where('roleId', $user['roleId'])->where('categoryId', $decoded->businessCategoryId)->findAll();
                foreach($rolePermissions as $key => $rolePermission){
                    $right = $rightModel->where('rightId', $rolePermission['rightId'])->where('isDeleted',0)->where('isActive',1)->where('parentRightId',0)->first();
                    
                    if ($right) {
                        // Store the parent menu details
                        $menuItem = [
                            'route' => $right['route'],
                            'name' => $right['route'],
                            'icon' => $right['iconUrl'],
                            'type' => 'sub', // Default to 'sub' in case it has children
                            'children' => [] // Initialize children array
                        ];
                
                        // Fetch the submenus (children) where parentRightId matches this rightId
                        $subMenus = $rightModel->where('parentRightId', $right['rightId'])->findAll();
                
                        if (empty($subMenus)) {
                            $menuItem['type'] = 'link'; // If no children, it's a simple link
                        } else {
                            foreach ($subMenus as $subMenu) {
                                $menuItem['children'][] = [
                                    'route' => $subMenu['route'],
                                    'name' => $subMenu['route'],
                                    'icon' => $subMenu['iconUrl'],
                                    'type' => 'link'
                                ];
                            }
                        }
                
                        // Store in the menu array
                        $menu[] = $menuItem;
                    }
                }
            }else{
                $menu[] = [];
            }
            
        }
        if(is_null($user)) {
            return $this->respond(['status' => false, 'message' => 'User not found.'], 404);
        }
         
        return $this->respond(['menu' => $menu], 200);
    }

    // public function login()
    // {
    //     $userModel = new UserModel();
    //     $rolePermissionModel = new RolePermissionModel();
    //     $rightModel = new RightModel();
    //     $roleModel = new RoleModel();
    //     $userBusiness = new UserBusiness();
    //     $business = new BusinessModel();

    //     $input = $this->request->getJSON();
        
    //     $user = $userModel->where('email', $input->email)->first();
    //     if(is_null($user)) {
    //         return $this->respond(['status' => false, 'message' => 'Email not found.'], 401);
    //     }
    //     $role = $roleModel->where('roleId', $user['roleId'])->first();
    //     $user['role'] = $role;    
  
    //     $pwd_verify = password_verify($input->password, $user['password']);
        
    //     if(!$pwd_verify) {
    //         return $this->respond(['status' => false, 'message' => 'Password is incorrect'], 401);
    //     }
 
    //     $key = "Exiaa@11";
    //     $iat = time(); // current timestamp value
    //     $exp = $iat + 3600;

    //     if($role['roleId'] == 1){
    //         $payload = array(
    //             "iss" => "Issuer of the JWT",
    //             "aud" => "Audience that the JWT",
    //             "sub" => "Subject of the JWT",
    //             "iat" => $iat, //Time the JWT issued at
    //             "exp" => $exp, // Expiration time of token
    //             "email" => $user['email'],
    //             "roleId" => $user['roleId'],
    //             "userId" => $user['userId']
    //         );
    //     }else{

    //         $userBusiness = $userBusiness->where('userId', $user['userId'])->findAll();

    //         if(empty($userBusiness)){
    //             return $this->respond(['status' => false, 'message' => 'User not assigned to any business, contact admin', 'data' => []], 401);
    //         }

    //         $user['business'] = $business->where('businessId', $userBusiness[0]['businessId'])->first();

    //         if($user['business']['isActive'] == 0 || $user['business']['isDeleted'] == 1){
    //             return $this->respond(['status' => false, 'message' => 'User is not active or deleted, contact admin', 'data' => []], 401);
    //         }

    //         if($user['business']['tenantName'] == null || $user['business']['tenantName'] == ''){
    //             return $this->respond(['status' => false, 'message' => 'Tenant not assigned to any business, contact admin', 'data' => []], 401);
    //         }

            
    
    //         $payload = array(
    //             "iss" => "Issuer of the JWT",
    //             "aud" => "Audience that the JWT",
    //             "sub" => "Subject of the JWT",
    //             "iat" => $iat, //Time the JWT issued at
    //             "exp" => $exp, // Expiration time of token
    //             "email" => $user['email'],
    //             "roleId" => $user['roleId'],
    //             "userId" => $user['userId'],
    //             "businessId" => $user['business']['businessId'],
    //             "tenantName" => $user['business']['tenantName'],
    //             "businessCategoryId" => $user['business']['businessCategoryId'],
    //         );
    //     }
         
    //     $token = JWT::encode($payload, $key, 'HS256');
    //     $refreshToken = bin2hex(random_bytes(32)); // Generate random token
 
    //     $response = [
    //         'access_token' => $token,
    //         'token_type' => 'Bearer',
    //         'expires_in' => $exp,
    //         'exp' => $exp,
    //         'refresh_token' => $refreshToken
    //     ];
         
    //     return $this->respond($response, 200);
    // }



//     public function login()
// {
//      $tenantService = new TenantService();
//             $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
//             $model = new UserModel($db);
//             $response = [
//                 "status" => true,
//                 "message" => "All Data Fetched",
//                 "data" => $model->findAll(),
//             ];
//             return $this->respond($response, 200);
// }
 

    

    //  public function login()
    // {
        
    //     $input = $this->request->getJSON();

    //     $cardemail = $input->cardemail ?? null;
    //     $password = $input->password ?? null;
    //     $selectedCard = $input->selectedCard ?? null;
        

    //     // $tenantService = new TenantService();
    //     // $tenantKey = $this->request->getHeaderLine('X-Tenant-Config');
    //     // $db = $tenantService->getTenantConfig($tenantKey);


    //     $userModel = new TenantUserModel();
    //     $user = $userModel->where('email', $input->email)->first();
    //     if(is_null($user)) {
    //         return $this->respond(['status' => false, 'message' => 'Email not found.'], 401);
    //     }
        
  
    //     $pwd_verify = password_verify($input->password, $user['password']);
        
    //     if(!$pwd_verify) {
    //         return $this->respond(['status' => false, 'message' => 'Password is incorrect'], 401);
    //     }
 
    //     $key = "Exiaa@11";
    //     $iat = time(); // current timestamp value
    //     $exp = $iat + 3600;

    //         $payload = array(
    //             "iss" => "Issuer of the JWT",
    //             "aud" => "Audience that the JWT",
    //             "sub" => "Subject of the JWT",
    //             "iat" => $iat, //Time the JWT issued at
    //             "exp" => $exp, // Expiration time of token
    //             "email" => $user['email'],
    //             "roleId" => $user['roleId'],
    //             "userId" => $user['userId']
    //         );
       
         
    //     $token = JWT::encode($payload, $key, 'HS256');
    //     $refreshToken = bin2hex(random_bytes(32)); // Generate random token
 
    //     $response = [
    //         'access_token' => $token,
    //         'token_type' => 'Bearer',
    //         'expires_in' => $exp,
    //         'exp' => $exp,
    //         'refresh_token' => $refreshToken
    //     ];
         
    //     return $this->respond($response, 200);
    // }


// public function login()
// {
//     try {
//         $input = $this->request->getJSON();

//         log_message('debug', 'Login Header: ' . $this->request->getHeaderLine('X-Tenant-Config'));

//         $tenantHeader = $this->request->getHeaderLine('X-Tenant-Config');

//         log_message('debug', 'Tenant Header: ' . $tenantHeader);

//         $tenantService = new TenantService();
//         $db = $tenantService->getTenantConfig($tenantHeader);

//         if (!$db) {
//             return $this->respond([
//                 'status' => false,
//                 'message' => 'Tenant configuration not found.'
//             ], 400);
//         }

//         // Proceed with authentication logic here

//         return $this->respond([
//             'status' => true,
//             'message' => 'Tenant configuration found and login logic goes here.'
//         ]);

//     } catch (\Throwable $e) {
//         log_message('error', 'Login Error: ' . $e->getMessage());
//         return $this->respond([
//             'status' => false,
//             'message' => 'An internal server error occurred.'
//         ], 500);
//     }
// }


public function login()
{
    $data = $this->request->getJSON(true);
    log_message('debug', 'Login Data: ' . print_r($data, true));
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;
    $selectedCard = $data['selectedCard'] ?? null;

    if (!$email || !$password) {
        return $this->respond([
            'status' => false,
            'message' => 'Email or password missing'
        ], 400);
    }

    // Step 1: Connect to tenant DB
    $tenant = $selectedCard;
    $tenantDb = \App\Libraries\TenantDatabase::connectTo($tenant);

    if (!$tenantDb) {
        return $this->respond([
            'status' => false,
            'message' => 'Could not connect to tenant database'
        ], 500);
    }

    // Step 2: Load TenantUserModel and inject the new DB connection
    $userModel = new \App\Models\TenantUserModel($tenantDb); // assumes constructor accepts $db
    $user = $userModel->where('email', $email)
                      ->where('isDeleted', 0)
                      ->where('isActive', 1)
                      ->first();

    if (!$user || !password_verify($password, $user['password'])) {
        return $this->respond([
            'status' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    return $this->respond([
        'status' => true,
        'message' => 'Login successful',
        'tenant' => $user,
    ], 200);
}


    public function register()
    {
        $input = $this->request->getJSON();

        $selectedCard = $input->selectedCard ?? null;


        $rules = [
            'name' => ['rules' => 'required|min_length[4]|max_length[255]'],
            'email' => ['rules' => 'required|min_length[4]|max_length[255]|valid_email|is_unique[user_mst.email]'],
            'mobile' => ['rules' => 'required|min_length[10]|max_length[10]|is_unique[user_mst.mobileNo]'],
            'password' => ['rules' => 'required|min_length[8]|max_length[255]'],
            'confirmPassword'  => [ 'label' => 'confirm password', 'rules' => 'matches[password]']
        ];
  
        if($this->validate($rules)){

         $dbName = $selectedCard;
         $tenantDb = \App\Libraries\TenantDatabase::connectTo($dbName);
         $model = new \App\Models\TenantUserModel($tenantDb);
           // $model = new UserModel();
            $data = [
                'name'     => $input->name,
                'mobileNo' => $input->mobile,
                'email'    => $input->email,
                'password' => password_hash($input->password, PASSWORD_DEFAULT),
                'roleId'   => 2
            ];
            $model->insert($data);
             
            return $this->respond(["status" => true, 'message' => 'Registered Successfully'], 200);
        }else{
            $response = [
                'status'=>false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response , 409);
             
        }
            
    }


    public function create()
    {
        $input = $this->request->getJSON();
        $rules = [
            'name' => ['rules' => 'required|min_length[4]|max_length[255]'],
            'email' => ['rules' => 'required|min_length[4]|max_length[255]|valid_email|is_unique[user_mst.email]'],
            'mobileNo' => ['rules' => 'required|min_length[10]|max_length[10]|is_unique[user_mst.mobileNo]'],
            'password' => ['rules' => 'required|min_length[8]|max_length[255]'],
        ];
  
        if($this->validate($rules)){
            $model = new UserModel();
            $data = [
                'name'     => $input->name,
                'mobileNo' => $input->mobileNo,
                'email'    => $input->email,
                'password' => password_hash($input->password, PASSWORD_DEFAULT)
            ];
            $model->insert($data);
             
            return $this->respond(["status" => true, 'message' => 'Created Successfully'], 200);
        }else{
            $response = [
                'status'=>false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response , 409);
             
        }
            
    }

    public function update()
    {
        $input = $this->request->getJSON();
    
        // Validation rules for updating user
        $rules = [
            'userId'   => ['rules' => 'required|numeric'],
            'name'     => ['rules' => 'required|min_length[4]|max_length[255]'],
            'email'    => ['rules' => 'required|min_length[4]|max_length[255]|valid_email'],
            'mobileNo' => ['rules' => 'required|min_length[10]|max_length[10]']
        ];
    
        if ($this->validate($rules)) {
            $model = new UserModel();
    
            // Check if the user exists
            $user = $model->find($input->userId);
            if (!$user) {
                return $this->fail(['status' => false, 'message' => 'User not found'], 404);
            }
    
            // Data to update
            $updateData = [
                'name'     => $input->name,
                'email'    => $input->email,
                'mobileNo' => $input->mobileNo
            ];
    
            // Check if the user is updating their email or mobile number
            if ($user['email'] !== $input->email) {
                $rules['email']['rules'] .= '|is_unique[user_mst.email]';
            }
            if ($user['mobileNo'] !== $input->mobileNo) {
                $rules['mobileNo']['rules'] .= '|is_unique[user_mst.mobileNo]';
            }
    
            // Validate unique email and mobile number separately
            if (!$this->validate($rules)) {
                return $this->fail([
                    'status'  => false,
                    'errors'  => $this->validator->getErrors(),
                    'message' => 'Invalid Inputs'
                ], 409);
            }
    
            // Update the user
            $model->update($input->userId, $updateData);
    
            return $this->respond(["status" => true, 'message' => 'User Updated Successfully'], 200);
        } else {
            return $this->fail([
                'status'  => false,
                'errors'  => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ], 409);
        }
    }
    
    public function delete()
    {
        $input = $this->request->getJSON();
        
        // Validation rules for userId
        $rules = [
            'userId' => ['rules' => 'required|numeric']
        ];
    
        if ($this->validate($rules)) {
            $model = new UserModel();
    
            // Check if the user exists
            $user = $model->find($input->userId);
            if (!$user) {
                return $this->fail(['status' => false, 'message' => 'User not found'], 404);
            }
    
            // Soft delete by updating isDeleted flag
            $updateData = [
                'isDeleted' => 1
            ];
            $deleted = $model->update($input->userId, $updateData);
    
            if ($deleted) {
                return $this->respond(["status" => true, 'message' => 'User Deleted Successfully'], 200);
            } else {
                return $this->fail(['status' => false, 'message' => 'Failed to delete user'], 500);
            }
        } else {
            return $this->fail([
                'status'  => false,
                'errors'  => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ], 409);
        }
    }
    


    // get all role api

    public function getAllRole()
    {
        $roles = new RoleModel;
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $roles->findAll()], 200);
    }
   

    public function createRole()
    {
        $input = $this->request->getJSON();
        $rules = [
            'roleName' => ['rules' => 'required'],
            'note'     => ['rules' => 'required']
        ];
    
        if ($this->validate($rules)) {
            $model = new RoleModel();
            $data = [
                'roleName' => $input->roleName,
                'note'     => $input->note
            ];
            $model->insert($data);
    
            return $this->respond(["status" => true, 'message' => 'Role Created Successfully'], 200);
        } else {
            $response = [
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response, 409);
        }
    }
    

    public function deleteRole()
    {
        $input = $this->request->getJSON();
        
        // Validation rules for the roleId
        $rules = [
            'roleId' => ['rules' => 'required|numeric']
        ];
    
        if ($this->validate($rules)) {
            $model = new RoleModel();
    
            // Check if the role exists
            $role = $model->find($input->roleId);
            if (!$role) {
                return $this->fail(['status' => false, 'message' => 'Role not found'], 404);
            }
    
            // Soft delete by setting isDeleted to 1
            $updateData = ['isDeleted' => 1];
            $model->update($input->roleId, $updateData);
    
            return $this->respond(["status" => true, 'message' => 'Role Deleted Successfully'], 200);
        } else {
            $response = [
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response, 409);
        }
    }
    

    
    public function updateRole()
    {
        $input = $this->request->getJSON();
    
        // Validation rules for updating role
        $rules = [
            'roleId'   => ['rules' => 'required|numeric'],
            'roleName' => ['rules' => 'required'],
            'note'     => ['rules' => 'required']
        ];
    
        if ($this->validate($rules)) {
            $model = new RoleModel();
    
            // Check if the role exists
            $role = $model->find($input->roleId);
            if (!$role) {
                return $this->fail(['status' => false, 'message' => 'Role not found'], 404);
            }
    
            // Data to update
            $updateData = [
                'roleName' => $input->roleName,
                'note'     => $input->note
            ];
    
            // Update the role
            $model->update($input->roleId, $updateData);
    
            return $this->respond(["status" => true, 'message' => 'Role Updated Successfully'], 200);
        } else {
            $response = [
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response, 409);
        }
    }



    // get all right api 

    public function getAllRight()
    {
        $rights = new RightModel;
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $rights->findAll()], 200);
    }

    public function createRight()
    {
        $input = $this->request->getJSON();
        $rules = [
            'rightName'  => ['rules' => 'required'],
            'rightLabel' => ['rules' => 'required'],
            'iconUrl'    => ['rules' => 'required'],
            'route'      => ['rules' => 'required']
        ];
    
        if ($this->validate($rules)) {
            $model = new RightModel();  // Assuming you have a RightModel
            $data = [
                'rightName'  => $input->rightName,
                'rightLabel' => $input->rightLabel,
                'iconUrl'    => $input->iconUrl,
                'route'      => $input->route
            ];
            $model->insert($data);
    
            return $this->respond(["status" => true, 'message' => 'Right Created Successfully'], 200);
        } else {
            $response = [
                'status'  => false,
                'errors'  => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response, 409);
        }
    }
    

    public function deleteRight()
    {
        $input = $this->request->getJSON();
        
        // Validation rules for the roleId
        $rules = [
            'rightId' => ['rules' => 'required|numeric']
        ];
    
        if ($this->validate($rules)) {
            $model = new RightModel();
    
            // Check if the role exists
            $right = $model->find($input->rightId);
            if (!$right) {
                return $this->fail(['status' => false, 'message' => 'Right not found'], 404);
            }
    
            // Soft delete by setting isDeleted to 1
            $updateData = ['isDeleted' => 1];
            $model->update($input->rightId, $updateData);
    
            return $this->respond(["status" => true, 'message' => 'Right Deleted Successfully'], 200);
        } else {
            $response = [
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response, 409);
        }
    }
    

    
    public function updateRight()
    {
        $input = $this->request->getJSON();
    
        // Validation rules for updating role
        $rules = [
            'rightName'  => ['rules' => 'required'],
            'rightLabel' => ['rules' => 'required'],
            'iconUrl'    => ['rules' => 'required'],
            'route'      => ['rules' => 'required']
        ];
    
        if ($this->validate($rules)) {
            $model = new RightModel();
    
            // Check if the role exists
            $right = $model->find($input->rightId);
            if (!$right) {
                return $this->fail(['status' => false, 'message' => 'Right not found'], 404);
            }
    
            // Data to update
            $updateData = [
                'rightName'  => $input->rightName,
                'rightLabel' => $input->rightLabel,
                'iconUrl'    => $input->iconUrl,
                'route'      => $input->route
            ];
    
            // Update the role
            $model->update($input->rightId, $updateData);
    
            return $this->respond(["status" => true, 'message' => 'Right Updated Successfully'], 200);
        } else {
            $response = [
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response, 409);
        }
    }

    // get all tenant api
    
    public function getAllTenant()
    {
        $tenants = new TenantModel;
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $tenants->findAll()], 200);
    }

    public function createTenant()
{
    $input = $this->request->getJSON();
    $rules = [
        'tenantName'   => ['rules' => 'required'],
        'databaseName' => ['rules' => 'required'],
        'username'     => ['rules' => 'required'],
        'password'     => ['rules' => 'required'],
        'hostUrl'      => ['rules' => 'required'],
        'businessId'   => ['rules' => 'required']
    ];

    if ($this->validate($rules)) {
        $model = new TenantModel();  // Assuming you have a TenantModel
        $data = [
            'tenantName'   => $input->tenantName,
            'databaseName' => $input->databaseName,
            'username'     => $input->username,
            'password'     => password_hash($input->password, PASSWORD_BCRYPT), // Encrypting password for security
            'hostUrl'      => $input->hostUrl,
            'businessId'   => $input->businessId
        ];
        $model->insert($data);

        return $this->respond(["status" => true, 'message' => 'Tenant Created Successfully'], 200);
    } else {
        $response = [
            'status'  => false,
            'errors'  => $this->validator->getErrors(),
            'message' => 'Invalid Inputs'
        ];
        return $this->fail($response, 409);
    }
}

public function deleteTenant()
{
    $input = $this->request->getJSON();

    // Validation rules for tenantId
    $rules = [
        'tenantId' => ['rules' => 'required|numeric']
    ];

    if ($this->validate($rules)) {
        $model = new TenantModel(); // Assuming you have a TenantModel

        // Check if the tenant exists
        $tenant = $model->find($input->tenantId);
        if (!$tenant) {
            return $this->fail(['status' => false, 'message' => 'Tenant not found'], 404);
        }

        // Soft delete by setting isDeleted to 1
        $updateData = ['isDeleted' => 1];
        $model->update($input->tenantId, $updateData);

        return $this->respond(["status" => true, 'message' => 'Tenant Deleted Successfully'], 200);
    } else {
        $response = [
            'status' => false,
            'errors' => $this->validator->getErrors(),
            'message' => 'Invalid Inputs'
        ];
        return $this->fail($response, 409);
    }
}



public function updateTenant()
{
    $input = $this->request->getJSON();

    // Validation rules for updating tenant
    $rules = [
        'tenantId'     => ['rules' => 'required|numeric'],
        'tenantName'   => ['rules' => 'required'],
        'databaseName' => ['rules' => 'required'],
        'username'     => ['rules' => 'required'],
        'password'     => ['rules' => 'required'],
        'hostUrl'      => ['rules' => 'required'],
        'businessId'   => ['rules' => 'required|numeric']
    ];

    if ($this->validate($rules)) {
        $model = new TenantModel(); // Assuming you have a TenantModel

        // Check if the tenant exists
        $tenant = $model->find($input->tenantId);
        if (!$tenant) {
            return $this->fail(['status' => false, 'message' => 'Tenant not found'], 404);
        }

        // Data to update
        $updateData = [
            'tenantName'   => $input->tenantName,
            'databaseName' => $input->databaseName,
            'username'     => $input->username,
            'password'     => $input->password,
            'hostUrl'      => $input->hostUrl,
            'businessId'   => $input->businessId
        ];

        // Update the tenant
        $model->update($input->tenantId, $updateData);

        return $this->respond(["status" => true, "message" => "Tenant Updated Successfully"], 200);
    } else {
        $response = [
            'status' => false,
            'errors' => $this->validator->getErrors(),
            'message' => 'Invalid Inputs'
        ];
        return $this->fail($response, 409);
    }
}





public function getAllBusiness()
{
    $businesses = new BusinessModel;
    return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $businesses->findAll()], 200);
}



public function createBusiness()
{
    $input = $this->request->getJSON();
    $rules = [
        'businessName' => ['rules' => 'required'],
        'tenantName' => ['rules' => 'required'],

        'businessDesc' => ['rules' => 'required'],
        'businessCategoryId' => ['rules' => 'required']
    ];

    if ($this->validate($rules)) {
        $model = new BusinessModel(); // Assuming you have a BusinessModel
        $data = [
            'businessName' => $input->businessName,
            'tenantName' => $input->tenantName,

            'businessDesc' => $input->businessDesc,
            'businessCategoryId' => $input->businessCategoryId
         
        ];
        
        $model->insert($data);

        return $this->respond(["status" => true, 'message' => 'Business Created Successfully'], 200);
    } else {
        $response = [
            'status'  => false,
            'errors'  => $this->validator->getErrors(),
            'message' => 'Invalid Inputs'
        ];
        return $this->fail($response, 409);
    }
}

public function updateBusiness()
{
    $input = $this->request->getJSON();

    // Validation rules for updating business
    $rules = [
        'businessId'   => ['rules' => 'required|numeric'],
        'businessName' => ['rules' => 'required|min_length[3]'],
        'businessDesc' => ['rules' => 'required|min_length[5]']
    ];

    if ($this->validate($rules)) {
        $model = new BusinessModel(); // Assuming you have a BusinessModel

        // Check if the business exists
        $business = $model->find($input->businessId);
        if (!$business) {
            return $this->fail(['status' => false, 'message' => 'Business not found'], 404);
        }

        // Data to update
        $updateData = [
            'businessName' => $input->businessName,
            'businessDesc' => $input->businessDesc,
            'businessCategoryId' => $input->businessCategoryId,
            'tenantName' => $input->tenantName
        ];

        // Update the business
        $model->update($input->businessId, $updateData);

        return $this->respond(["status" => true, 'message' => 'Business Updated Successfully'], 200);
    } else {
        $response = [
            'status' => false,
            'errors' => $this->validator->getErrors(),
            'message' => 'Invalid Inputs'
        ];
        return $this->fail($response, 409);
    }
}



public function deleteBusiness()
{
    $input = $this->request->getJSON();
    
    // Validation rules for businessId
    $rules = [
        'businessId' => ['rules' => 'required|numeric']
    ];

    if ($this->validate($rules)) {
        $model = new BusinessModel(); // Assuming you have a BusinessModel

        // Check if the business exists
        $business = $model->find($input->businessId);
        if (!$business) {
            return $this->fail(['status' => false, 'message' => 'Business not found'], 404);
        }

        // Soft delete by setting isDeleted to 1
        $updateData = ['isDeleted' => 1];
        $model->update($input->businessId, $updateData);

        return $this->respond(["status" => true, 'message' => 'Business Deleted Successfully'], 200);
    } else {
        $response = [
            'status' => false,
            'errors' => $this->validator->getErrors(),
            'message' => 'Invalid Inputs'
        ];
        return $this->fail($response, 409);
    }
}


public function getBusinessesPaging()
{
    $input = $this->request->getJSON();
    $businessModel = new BusinessModel(); // Assuming you have a BusinessModel

    // Get the page number from the input, default to 1 if not provided
    $page = isset($input->page) ? $input->page : 1;
    // Define the number of items per page
    $perPage = isset($input->perPage) ? $input->perPage : 50;

    // Fetch paginated business data
    $businesses = $businessModel->paginate($perPage, 'default', $page);
    $pager = $businessModel->pager;

    $response = [
        "status" => true,
        "message" => "All Businesses Fetched",
        "data" => $businesses,
        "pagination" => [
            "currentPage" => $pager->getCurrentPage(),
            "totalPages" => $pager->getPageCount(),
            "totalItems" => $pager->getTotal(),
            "perPage" => $perPage
        ]
    ];

    return $this->respond($response, 200);
}




public function getAllBusinessCategory()
{
    $categories = new BusinessCategoryModel;
    return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $categories->findAll()], 200);
}



public function getAllTenantName()
{
    $tenants = new TenantModel;
    return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $tenants->findAll()], 200);
}

public function getAllPermissionByCategory()
{
    $input = $this->request->getJSON();
    $categoryId = $input->categoryId;
    $model = new RolePermissionModel;
    $rightsModel = new RightModel;
    $permissions = $model->where('categoryId', $categoryId)->findAll();

    return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $permissions], 200);
}

public function updatePermissions()
{
    $input = $this->request->getJSON();
    $model = new RolePermissionModel;
    foreach ($input as $permission) {
        if(isset($permission->permissionId)){
            $model->update($permission->permissionId, $permission);
            continue;
        }else{
            $model->insert($permission);
            continue;
        }
    }
    return $this->respond(["status" => true, "message" => "Permissions Updated Successfully", "data" => []], 200);

}

public function assignBusiness()
{
    $input = $this->request->getJSON();
    $model = new UserBusiness;
    $model->insertBatch($input);
    return $this->respond(["status" => true, "message" => "Business Assigned Successfully", "data" => []], 200);

}



// public function addRestaurantDetails()
// {
//     $tenantService = new TenantService();
//     $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

//     $restaurantModel = new RestaurantDetails($db);

//     // Get JSON input
//     $data = $this->request->getJSON(true);

//     // Validate required fields
//     if (!isset($data['resTitle'], $data['resName'], $data['resAddress'], $data['resContactNo'])) {
//         return $this->respond([
//             'status' => false,
//             'message' => 'Required fields missing: resTitle, resName, resAddress, or resContactNo',
//         ], 400);
//     }

//     // Prepare data for insert
//     $insertData = [
//         'resTitle'       => $data['resTitle'],
//         'resName'        => $data['resName'],
//         'resAddress'     => $data['resAddress'],
//         'resContactNo'   => $data['resContactNo'],
//         'resTrn'         => $data['resTrn'] ?? null,
//         'resLogo'  => $data['resLogo'] ?? null,  // store base64 string directly, or handle separately if needed
//         'isActive'       => $data['isActive'] ?? 1,
//         'createdDate'    => date('Y-m-d H:i:s', strtotime($data['createdDate'] ?? 'now')),
//     ];

//     // Insert data into DB
//     if ($restaurantModel->insert($insertData)) {
//         return $this->respond([
//             'status' => true,
//             'message' => 'Restaurant details added successfully',
//             'data' => $insertData
//         ], 200);
//     } else {
//         return $this->respond([
//             'status' => false,
//             'message' => 'Failed to add restaurant details',
//             'errors' => $restaurantModel->errors()
//         ], 500);
//     }
// }
 public function addRestaurantDetails()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $resturantModel = new RestaurantDetails($db);

    // Get JSON input

    $data = $this->request->getJSON(true);

    // Validate required fields
     if (!isset($data['resTitle']) ||
         !isset($data['resName']) ||
         !isset($data['resAddress']) ||
         !isset($data['resContactNo'])
           ) {
         return $this->respond([
        'status' => false,
        'message' => 'Required fields missing:  or createdBy',
           ], 400); // Bad Request
         }

    // Format and prepare the data
    $insertData = [
                'resTitle'       => $data['resTitle'],
                'resName'        => $data['resName'],
                'resAddress'     => $data['resAddress'],
                'resContactNo'   => $data['resContactNo'],
                'resTrn'         => $data['resTrn'] ?? null,
                'resLogo'  => $data['resLogo'] ?? null,  // store base64 string directly, or handle separately if needed
                'isActive'       => $data['isActive'] ?? 1,
                'createdDate'    => date('Y-m-d H:i:', strtotime($data['createdDate'] ?? 'now')),
            ];

    // Insert data
    if ($resturantModel->insert($insertData)) {
        return $this->respond([
            'status' => true,
            'message' => 'Address added successfully',
            'data' => $insertData
        ], 200);
    } else {
        return $this->respond([
            'status' => false,
            'message' => 'Failed to add address',
            'errors' => $restaurantModel->errors()
        ], 500);
    }
}

// public function addRestaurantDetails()
// {
//     $input = $this->request->getJSON();
//     $rules = [
//         'resTitle'  => ['rules' => 'required'],
//         'resName' => ['rules' => 'required'],
//         'resAddress'    => ['rules' => 'required'],
//         'resContactNo'      => ['rules' => 'required'],
//         'resTrn'      => ['rules' => 'required'],
//         'resLogo'      => ['rules' => 'required'],
//         'isActive'      => ['rules' => 'required'],
//         'createdDate'      => ['rules' => 'required'],

//     ];

//     if ($this->validate($rules)) {
//         $model = new RightModel();  // Assuming you have a RightModel
//         $data = [
//             'rightName'  => $input->rightName,
//             'rightLabel' => $input->rightLabel,
//             'iconUrl'    => $input->iconUrl,
//             'route'      => $input->route
//         ];
//         $model->insert($data);

//         return $this->respond(["status" => true, 'message' => 'Right Created Successfully'], 200);
//     } else {
//         $response = [
//             'status'  => false,
//             'errors'  => $this->validator->getErrors(),
//             'message' => 'Invalid Inputs'
//         ];
//         return $this->fail($response, 409);
//     }
// }


public function updateRestaurantDetails()
{

    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $restaurantModel = new RestaurantDetails($db);

    // Get input data
    $data = $this->request->getJSON(true);


    $resId = $data['resId'];


    // Check if addon exists
    $existing = $restaurantModel->find($resId);
    if (!$existing) {
        return $this->respond([
            'status' => false,
            'message' => 'Resturant not found'
        ], 404);
    }

    // Prepare fields to update
    $updateData = [];

    if (isset($data['resTitle'])) {
        $updateData['resTitle'] = $data['resTitle'];
    }
    if (isset($data['resName'])) {
        $updateData['resName'] = $data['resName'];
    }

    if (isset($data['resAddress'])) {
        $updateData['resAddress'] = $data['resAddress'];
    }

    if (isset($data['resContactNo'])) {
        $updateData['resContactNo'] = $data['resContactNo'];
    }
    if (isset($data['resTrn'])) {
        $updateData['resTrn'] = $data['resTrn'];
    }
    if (isset($data['resLogo'])) {
        $updateData['resLogo'] = $data['resLogo'];
    }
    if (isset($data['isActive'])) {
        $updateData['isActive'] = $data['isActive'];
    }
    

    // Perform the update
    if ($restaurantModel->update($resId, $updateData)) {
        return $this->respond([
            'status' => true,
            'message' => 'Restaurant Detail updated successfully',
            'data' => $updateData
        ], 200);
    } else {
        return $this->respond([
            'status' => false,
            'message' => 'Failed to update Restirant',
            'errors' => $restaurantModel->errors()
        ], 500);
    }


    // if (!$resId) {
    //     return $this->respond([
    //         'status' => false,
    //         'message' => 'Restaurant ID is required for update',
    //     ], 400);
    // }

    // $tenantService = new TenantService();
    // $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    // $restaurantModel = new RestaurantDetails($db);

    // // Check if the restaurant exists
    // $existing = $restaurantModel->find($resId);
    // if (!$existing) {
    //     return $this->respond([
    //         'status' => false,
    //         'message' => 'Restaurant not found',
    //     ], 404);
    // }

    // // Get JSON input
    // $data = $this->request->getJSON(true);

    // // Prepare data to update - only update fields sent in request
    // $updateData = [];

    // $fields = ['resTitle', 'resName', 'resAddress', 'resContactNo', 'resTrn', 'resLogo', 'isActive', 'modifiedDate', 'modifiedBy'];
    // foreach ($fields as $field) {
    //     if (isset($data[$field])) {
    //         $updateData[$field] = $data[$field];
    //     }
    // }

    // // If modifiedDate not set, set to now
    // if (!isset($updateData['modifiedDate'])) {
    //     $updateData['modifiedDate'] = date('Y-m-d H:i:s');
    // }

    // if ($restaurantModel->update($resId, $updateData)) {
    //     return $this->respond([
    //         'status' => true,
    //         'message' => 'Restaurant details updated successfully',
    //         'data' => $updateData
    //     ], 200);
    // } else {
    //     return $this->respond([
    //         'status' => false,
    //         'message' => 'Failed to update restaurant details',
    //         'errors' => $restaurantModel->errors()
    //     ], 500);
    // }
}


public function getAllRestaurantDetails()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $restaurantModel = new RestaurantDetails($db);

    $restaurants = $restaurantModel->where('isDeleted', 0)->findAll();

    return $this->respond([
        'status' => true,
        'data' => $restaurants
    ], 200);
}


}
