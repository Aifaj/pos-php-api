<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\TenantService;
use CodeIgniter\API\ResponseTrait;
use Config\Database;
use App\Models\TenantUserModel;
use App\Models\CustomerModel;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class TenantUser extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        //
    }

















    

    // emailId Password

    public function loginWithMobileUid()
    {
        $input = $this->request->getJSON();
        log_message('error', 'mobileNumber: '.$input->mobileNumber);
        log_message('error', 'uid: '.$input->uid);
        $tenantService = new TenantService();
        
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $tenantUserModel = new TenantUserModel($db);
        
        $tenantUser = $tenantUserModel->where('mobileNo', $input->mobileNumber)->first();
        if(is_null($tenantUser)) {
            $tenantUserInput = [
                'uid' => $input->uid,
                'mobileNo' => $input->mobileNumber
            ];
            if($insertId = $tenantUserModel->insert($tenantUserInput)) {
                $key = "Exiaa@11";
                $iat = time(); // current timestamp value
                $exp = $iat + 3600;

                $payload = array(
                    "iss" => "Issuer of the JWT",
                    "aud" => "Audience that the JWT",
                    "sub" => "Subject of the JWT",
                    "iat" => $iat, //Time the JWT issued at
                    "exp" => $exp, // Expiration time of token
                    "uid" => $input->uid,
                    "mobileNo" => $input->mobileNumber,
                    "userId" => $insertId
                );
            } else {
                return $this->respond(['status' => false, 'message' => 'User creation failed'], 401);
            }
        }else{
            if($tenantUser['uid'] == $input->uid){
                $key = "Exiaa@11";
                $iat = time(); // current timestamp value
                $exp = $iat + 3600;

                $payload = array(
                    "iss" => "Issuer of the JWT",
                    "aud" => "Audience that the JWT",
                    "sub" => "Subject of the JWT",
                    "iat" => $iat, //Time the JWT issued at
                    "exp" => $exp, // Expiration time of token
                    "uid" => $tenantUser['uid'],
                    "mobileNo" => $tenantUser['mobileNo'],
                    "userId" => $tenantUser['userId']
                );
            }else{
                return $this->respond(['status' => false, 'message' => 'Mobile number and UID does not match'], 401);
            }
            
        }
         
        $token = JWT::encode($payload, $key, 'HS256');
        $refreshToken = bin2hex(random_bytes(32)); // Generate random token
 
        $response = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $exp,
            'exp' => $exp,
            'refresh_token' => $refreshToken
        ];
         
        return $this->respond($response, 200);
    }

    public function getProfile()
    {
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
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
        $tenantUserModel = new TenantUserModel($db);

        $user = $tenantUserModel->where('userId', $decoded->userId)->first();
        if(is_null($user)) {
            return $this->respond(['status' => false, 'message' => 'User not found'], 404);
        }
        return $this->respond(['status' => true, 'message' => 'User found', 'data' => $user], 200);
    }


    
    public function create()
    {
        $input = $this->request->getPost();
        
        $rules = [
            'name' => ['rules' => 'required'],
            'email' => ['rules' => 'required|valid_email'],
            'mobileNo' => ['rules' => 'required'],
            'country' => ['rules' => 'required'],
            'location' => ['rules' => 'required'],
            'userType' => ['rules' => 'required'],
            'town' => ['rules' => 'required'],
            'postcode' => ['rules' => 'required'],
            'photoUrl' => isset($input['photoUrl']) ? $input['photoUrl'] : null
        ];

        if (!$this->validate($rules)) {
            return $this->fail(['status' => false, 'errors' => $this->validator->getErrors(), 'message' => 'Invalid Inputs'], 409);
        }

        $key = "Exiaa@11";
        $header = $this->request->getHeader("Authorization");
        $token = null;

        if (!empty($header) && preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            $token = $matches[1];
        }

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
        } catch (Exception $e) {
            return $this->fail(['status' => false, 'message' => 'Invalid Token'], 401);
        }

        // Handle Image Upload
        $photoUrl = $this->request->getFile('photoUrl');
        if ($photoUrl && $photoUrl->isValid() && !$photoUrl->hasMoved()) {
            $photoUrlPath = FCPATH . 'uploads/' . $decoded->tenantName . '/itemImages/';
            if (!is_dir($photoUrlPath)) mkdir($photoUrlPath, 0777, true);

            $photoUrlName = $photoUrl->getRandomName();
            $photoUrl->move($photoUrlPath, $photoUrlName);

            $input['photoUrl'] = $decoded->tenantName . '/itemImages/' . $photoUrlName;
        }

        // Insert Data
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new TenantUserModel($db);
        $model->insert($input);

        return $this->respond(['status' => true, 'message' => 'User Added Successfully'], 200);
    }

    public function update()
    {
        $input = $this->request->getPost();
    
        // Validate User ID
        if (!isset($input['userId']) || empty($input['userId'])) {
            return $this->fail(['status' => false, 'message' => 'User ID is required'], 400);
        }
    
        // Define Validation Rules
        $rules = [
            'name'      => 'required',
            'email'     => 'required|valid_email',
            'mobileNo'  => 'required',
            'country'   => 'required',
            'location'  => 'required',
            'userType'  => 'required',
            'town'      => 'required',
            'postcode'  => 'required'
        ];
    
        // Validate Input
        if (!$this->validate($rules)) {
            return $this->fail(['status' => false, 'message' => 'Validation Failed', 'errors' => $this->validator->getErrors()], 400);
        }
    
        // Get Database Connection
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
    
        if (!$db) {
            return $this->fail(['status' => false, 'message' => 'Database connection failed'], 500);
        }
    
        // Update User Record in tenant_user
        $tenantUserModel = new TenantUserModel($db);
        if (!$tenantUserModel->update($input['userId'], $input)) {
            return $this->fail(['status' => false, 'message' => 'Update failed'], 500);
        }
    
        // If userType is 'Customer', insert into customer_mst
        if ($input['userType'] === 'Customer') {
            $customerModel = new CustomerModel($db);
            $customerData = [
                'name'              => $input['name'],
                'mobileNo'          => $input['mobileNo'],
                'emailId'           => $input['email'],
                'gender'            => isset($input['gender']) ? $input['gender'] : null,
                'dateOfBirth'       => isset($input['dateOfBirth']) ? $input['dateOfBirth'] : null,
                'alternateMobileNo' => isset($input['alternateMobileNo']) ? $input['alternateMobileNo'] : null,
                'createdBy'         => $input['userId'],
                'createdDate'       => date('Y-m-d H:i:s'),
                'isActive'          => 1,
                'isDeleted'         => 0
            ];
    
            // Insert into customer_mst and get the inserted customerId
            $customerId = $customerModel->insert($customerData);
    
            if ($customerId) {
                // Update tenant_user with the customerId in userTypeSpeid
                $tenantUserModel->update($input['userId'], ['userTypeSpeid' => $customerId]);
            }
        }
    
        return $this->respond(['status' => true, 'message' => 'User Updated Successfully'], 200);
    }
    


}


    