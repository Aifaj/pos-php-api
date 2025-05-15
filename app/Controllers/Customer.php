<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\CustomerModel;
use Config\Database;
use App\Libraries\TenantService;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class Customer extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        // Insert the product data into the database
        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        // Load UserModel with the tenant database connection
        $CustomerModel = new CustomerModel($db);
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $CustomerModel->findAll()], 200);
    }

    public function getCustomersPaging()
    {
        $input = $this->request->getJSON();
    
        // Get the page number from the input, default to 1 if not provided
        $page = isset($input->page) ? $input->page : 1;
        $perPage = isset($input->perPage) ? $input->perPage : 10;
        $sortField = isset($input->sortField) ? $input->sortField : 'customerId';
        $sortOrder = isset($input->sortOrder) ? $input->sortOrder : 'asc';
        $search = isset($input->search) ? $input->search : '';
        $filter = $input->filter;
    
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        // Load CustomerModel with the tenant database connection
        $customerModel = new CustomerModel($db);
    
        $query = $customerModel;
    
        if (!empty($filter)) {
            $filter = json_decode(json_encode($filter), true);
    
            foreach ($filter as $key => $value) {
                if (in_array($key, ['name', 'mobileNo', 'email', 'customerCode'])) {
                    $query->like($key, $value); // LIKE filter for specific fields
                } else if ($key === 'createdDate') {
                    $query->where($key, $value); // Exact match filter for createdDate
                }
            }
    
            // Apply Date Range Filter (startDate and endDate)
            if (!empty($filter['startDate']) && !empty($filter['endDate'])) {
                $query->where('createdDate >=', $filter['startDate'])
                      ->where('createdDate <=', $filter['endDate']);
            }
    
            // Apply Last 7 Days Filter if requested
            if (!empty($filter['dateRange']) && $filter['dateRange'] === 'last7days') {
                $last7DaysStart = date('Y-m-d', strtotime('-7 days'));  // 7 days ago from today
                $query->where('createdDate >=', $last7DaysStart);
            }
    
            // Apply Last 30 Days Filter if requested
            if (!empty($filter['dateRange']) && $filter['dateRange'] === 'last30days') {
                $last30DaysStart = date('Y-m-d', strtotime('-30 days'));  // 30 days ago from today
                $query->where('createdDate >=', $last30DaysStart);
            }
        }
    
        // Ensure that the "deleted" status is 0 (active records)
        $query->where('isDeleted', 0);
    
        // Apply Sorting
        if (!empty($sortField) && in_array(strtoupper($sortOrder), ['ASC', 'DESC'])) {
            $query->orderBy($sortField, $sortOrder);
        }
    
        // Get Paginated Results
        $customers = $query->paginate($perPage, 'default', $page);
        $pager = $customerModel->pager;
    
        $response = [
            "status" => true,
            "message" => "All Customer Data Fetched",
            "data" => $customers,
            "pagination" => [
                "currentPage" => $pager->getCurrentPage(),
                "totalPages" => $pager->getPageCount(),
                "totalItems" => $pager->getTotal(),
                "perPage" => $perPage
            ]
        ];
    
        return $this->respond($response, 200);
    }
    
    

    public function getCustomersWebsite()
    {
        // Insert the product data into the database
        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        
        // Load UserModel with the tenant database connection
        $CustomerModel = new CustomerModel($db);
        $customer = $CustomerModel->orderBy('createdDate', 'DESC')->where('isActive', 1)->where('isDeleted', 0)->findAll();
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $customer], 200);
    }

   public function create()
{
    // Retrieve the input data from the request
    $input = $this->request->getPost();
    
    // Define validation rules for required fields
    $rules = [
        'name' => ['rules' => 'required'],
        'mobileNo' => ['rules' => 'required'],
        'tenantName' => ['rules' => 'required'] // Assuming tenantName comes from post data now
    ];

    if ($this->validate($rules)) {
        $tenantName = $input['tenantName'];

        // Handle image upload for the profile picture
        $profilePic = $this->request->getFile('profilePic');
        $profilePicName = null;

        if ($profilePic && $profilePic->isValid() && !$profilePic->hasMoved()) {
            // Define the upload path for the profile picture
            $profilePicPath = FCPATH . 'uploads/' . $tenantName . '/customerImages/';
            if (!is_dir($profilePicPath)) {
                mkdir($profilePicPath, 0777, true); // Create directory if it doesn't exist
            }

            // Move the file to the desired directory with a unique name
            $profilePicName = $profilePic->getRandomName();
            $profilePic->move($profilePicPath, $profilePicName);

            // Add the profilePic path to input
            $input['profilePic'] = $tenantName . '/customerImages/' . $profilePicName;
        }

        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new CustomerModel($db);
        $model->insert($input);

        return $this->respond(['status' => true, 'message' => 'Customer Added Successfully'], 200);
    } else {
        // If validation fails, return the error messages
        $response = [
            'status' => false,
            'errors' => $this->validator->getErrors(),
            'message' => 'Invalid Inputs',
        ];
        return $this->fail($response, 409);
    }
}




    public function update()
    {
        $input = $this->request->getPost();
        
        // Validation rules for the vendor
        $rules = [
            'customerId' => ['rules' => 'required|numeric'], // Ensure vendorId is provided and is numeric
        ];

        // Validate the input
        if ($this->validate($rules)) {
            $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new CustomerModel($db);

            // Retrieve the vendor by vendorId
            $customerId = $input['customerId'];
            $customer = $model->find($customerId); // Assuming find method retrieves the vendor
            
           


            if (!$customer) {
                return $this->fail(['status' => false, 'message' => 'Customer not found'], 404);
            }

            
            $updateData = [
                'name' => $input['name'],  // Corrected here
                'customerCode' => $input['customerCode'],  // Corrected here
                'mobileNo' => $input['mobileNo'],  // Corrected here
                'alternateMobileNo' => $input['alternateMobileNo'],  // Corrected here
                'emailId' => $input['emailId'],  // Corrected here
                'dateOfBirth' => $input['dateOfBirth'],  // Corrected here
                'gender' => $input['gender'],  // Corrected here

                
    
            ];     

            // Handle cover image update
            $profilePic = $this->request->getFile('profilePic');
            if ($profilePic && $profilePic->isValid() && !$profilePic->hasMoved()) {
                // Handle cover image upload as in create() method
                $key = "Exiaa@11";
                $header = $this->request->getHeader("Authorization");
                $token = null;
    
                // Extract the token from the header
                if (!empty($header)) {
                    if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                        $token = $matches[1];
                    }
                }
    
                $decoded = JWT::decode($token, new Key($key, 'HS256'));
                $profilePicPath = FCPATH . 'uploads/' . $decoded->tenantName . '/customerImages/';
    
                // Create directory if it doesn't exist
                if (!is_dir($profilePicPath)) {
                    mkdir($profilePicPath, 0777, true);
                }
    
                // Save the image and get the name
                $profilePicName = $profilePic->getRandomName();
                $profilePic->move($profilePicPath, $profilePicName);
    
                // Add the new cover image URL to the update data
                $input['profilePic'] = $decoded->tenantName . '/customerImages/' . $profilePicName;
                $updateData['profilePic'] = $input['profilePic'];  // Add cover image URL to update data
            }


            // Update the vendor with new data
            $updated = $model->update($customerId, $updateData);


            if ($updated) {
                return $this->respond(['status' => true, 'message' => 'Vendor Updated Successfully'], 200);
            } else {
                return $this->fail(['status' => false, 'message' => 'Failed to update vendor'], 500);
            }
        } else {
            // Validation failed
            $response = [
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response, 409);
        }
    }




    public function delete()
{
    $input = $this->request->getJSON();

    // Validation rules for the customer
    $rules = [
        'customerId' => ['rules' => 'required'], // Ensure customerId is provided
    ];

    // Validate the input
    if ($this->validate($rules)) {
        // Connect to the tenant's database
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));   
        $model = new CustomerModel($db);

        // Retrieve the customer by customerId
        $customerId = $input->customerId;
        $customer = $model->where('customerId', $customerId)->where('isDeleted', 0)->first(); // Only find active customers

        if (!$customer) {
            return $this->fail(['status' => false, 'message' => 'Customer not found or already deleted'], 404);
        }

        // Perform a soft delete (mark as deleted instead of removing the record)
        $updateData = [
            'isDeleted' => 1,
        ];
        $deleted = $model->update($customerId, $updateData);
        

        if ($deleted) {
            return $this->respond(['status' => true, 'message' => 'Customer marked as deleted'], 200);
        } else {
            return $this->fail(['status' => false, 'message' => 'Failed to delete customer'], 500);
        }
    } else {
        // Validation failed
        $response = [
            'status' => false,
            'errors' => $this->validator->getErrors(),
            'message' => 'Invalid Inputs'
        ];
        return $this->fail($response, 409);
    }
}


    public function uploadPageProfile()
    {
        // Retrieve form fields
        $customerId = $this->request->getPost('customerId'); // Example field

        // Retrieve the file
        $file = $this->request->getFile('photoUrl');

        
        // Validate file
        if (!$file->isValid()) {
            return $this->fail($file->getErrorString());
        }

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
            return $this->fail('Invalid file type. Only JPEG, PNG, and GIF are allowed.');
        }

        // Validate file type and size
        if ($file->getSize() > 2048 * 1024) {
            return $this->fail('Invalid file type or size exceeds 2MB');
        }

        // Generate a random file name and move the file
        $newName = $file->getRandomName();
        $filePath = '/uploads/' . $newName;
        $file->move(WRITEPATH . '../public/uploads', $newName);

        // Save file and additional data in the database
        $data = [
            'photoUrl' => $newName,
        ];

        $model = new CustomerModel();
        $model->update($customerId,$data);

        return $this->respond([
            'status' => 201,
            'message' => 'File and data uploaded successfully',
            'data' => $data,
        ]);
    }
}
