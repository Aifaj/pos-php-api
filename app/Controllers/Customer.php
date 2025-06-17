<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\CustomerModel;
use App\Models\CustomerAddress;
use App\Models\CustomerDiscount;


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
    try {
        // Retrieve the input data from the request
        $input = $this->request->getPost();

        // Define validation rules
        $rules = [
            'name' => ['rules' => 'required'],
            'mobileNo' => ['rules' => 'required'],
            'tenantName' => ['rules' => 'required'],
        ];

        if (!$this->validate($rules)) {
            return $this->fail([
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs',
            ], 409);
        }

        $tenantName = $input['tenantName'];
        $base64Image = $input['profilePic'] ?? null;
        $profilePicName = null;

        // Handle base64 image upload if provided
        if ($base64Image && strpos($base64Image, 'base64') !== false) {
            $profilePicPath = FCPATH . 'uploads/' . $tenantName . '/customerImages/';
            if (!is_dir($profilePicPath)) {
                mkdir($profilePicPath, 0777, true);
            }

            // Split base64 string
            [$typeInfo, $imageData] = explode(';base64,', $base64Image);
            $imageType = str_replace('data:image/', '', $typeInfo);
            $profilePicName = uniqid('profile_', true) . '.' . $imageType;

            // Decode and save
            $fullImagePath = $profilePicPath . $profilePicName;
            file_put_contents($fullImagePath, base64_decode($imageData));

            // Update path in input for DB
            $input['profilePic'] = $tenantName . '/customerImages/' . $profilePicName;
        }

        // Connect to tenant DB
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

        // Insert into customer model
        $model = new CustomerModel($db);
        $model->insert($input);

        return $this->respond(['status' => true, 'message' => 'Customer Added Successfully'], 200);

    } catch (\Throwable $e) {
        return $this->fail([
            'status' => false,
            'message' => 'Server Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
}



    public function update()
{
    try {
        $input = $this->request->getPost();

        // Validation rules
        $rules = [
            'customerId' => ['rules' => 'required|numeric'],
            'name' => ['rules' => 'required'],
            'mobileNo' => ['rules' => 'required'],
            'tenantName' => ['rules' => 'required'],
        ];

        if (!$this->validate($rules)) {
            return $this->fail([
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs',
            ], 409);
        }

        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new CustomerModel($db);

        $customerId = $input['customerId'];
        $customer = $model->find($customerId);

        if (!$customer) {
            return $this->fail(['status' => false, 'message' => 'Customer not found'], 404);
        }

        $updateData = [
            'name' => $input['name'] ?? '',
            'customerCode' => $input['customerCode'] ?? '',
            'mobileNo' => $input['mobileNo'] ?? '',
            'alternateMobileNo' => $input['alternateMobileNo'] ?? '',
            'emailId' => $input['emailId'] ?? '',
            'dateOfBirth' => $input['dateOfBirth'] ?? '',
            'gender' => $input['gender'] ?? '',
        ];

        $profilePicPath = FCPATH . 'uploads/' . $input['tenantName'] . '/customerImages/';
        if (!is_dir($profilePicPath)) {
            mkdir($profilePicPath, 0777, true);
        }

        // Handle base64 or file image upload
        $base64Image = $input['profilePic'] ?? null;
        $uploadedFile = $this->request->getFile('profilePic');

        if ($base64Image && strpos($base64Image, 'base64') !== false) {
            // Handle base64 image update
            [$typeInfo, $imageData] = explode(';base64,', $base64Image);
            $imageType = str_replace('data:image/', '', $typeInfo);
            $profilePicName = uniqid('profile_', true) . '.' . $imageType;

            $fullImagePath = $profilePicPath . $profilePicName;
            file_put_contents($fullImagePath, base64_decode($imageData));

            $updateData['profilePic'] = $input['tenantName'] . '/customerImages/' . $profilePicName;
        } elseif ($uploadedFile && $uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
            // Handle file upload image update
            $profilePicName = $uploadedFile->getRandomName();
            $uploadedFile->move($profilePicPath, $profilePicName);

            $updateData['profilePic'] = $input['tenantName'] . '/customerImages/' . $profilePicName;
        }

        $updated = $model->update($customerId, $updateData);

        if ($updated) {
            return $this->respond(['status' => true, 'message' => 'Customer Updated Successfully'], 200);
        } else {
            return $this->fail(['status' => false, 'message' => 'Failed to update customer'], 500);
        }

    } catch (\Throwable $e) {
        return $this->fail([
            'status' => false,
            'message' => 'Server Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
}




public function delete()
{
    $input = $this->request->getJSON(true); // true returns associative array

    // Validation rules for the customer
    $rules = [
        'customerId' => ['rules' => 'required'],
    ];

    // Validate the input
    if (!$this->validate($rules)) {
        // Validation failed
        return $this->fail([
            'status' => false,
            'errors' => $this->validator->getErrors(),
            'message' => 'Invalid Inputs'
        ], 409);
    }

    $customerId = $input['customerId'];

    // Connect to the tenant's database
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
    $model = new CustomerModel($db);

    // Retrieve the customer
    $customer = $model->where('customerId', $customerId)->where('isDeleted', 0)->first();

    if (!$customer) {
        return $this->fail([
            'status' => false,
            'message' => 'Customer not found or already deleted'
        ], 404);
    }

    // Perform soft delete
    $success = $model->update($customerId, ['isDeleted' => 1]);

    if ($success) {
        return $this->respond([
            'status' => true,
            'message' => 'Customer marked as deleted'
        ], 200);
    } else {
        return $this->fail([
            'status' => false,
            'message' => 'Failed to delete customer'
        ], 500);
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



    

    public function getCustomerAddress()
{
    $input = $this->request->getJSON();

    if (!isset($input->customerId)) {
        return $this->respond(["status" => false, "message" => "customerId is required"], 400);
    }

    // Get tenant DB connection
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    // Load model with tenant DB
    $model = new \App\Models\CustomerAddress($db);

    // Fetch addresses filtered by customerId
    $addresses = $model
        ->where('customerId', $input->customerId)
        ->where('isDeleted', 0) // Optional: if soft delete field is used
        ->findAll();

    return $this->respond([
        "status" => true,
        "message" => "Customer addresses fetched",
        "data" => $addresses
    ], 200);
}


public function getAllCustomerAddress()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $itemModel = new CustomerAddress($db);

    // Fetch only addons where isDeleted = 0
    $address = $itemModel->where('isDeleted', 0)->findAll();

    $response = [
        "status" => true,
        "message" => "All Data Fetched",
        "data" => $address,
    ];

    return $this->respond($response, 200);
}

public function getallCustomer()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $itemModel = new CustomerModel($db);

    // Fetch only addons where isDeleted = 0
    $address = $itemModel->where('isDeleted', 0)->findAll();

    $response = [
        "status" => true,
        "message" => "All Data Fetched",
        "data" => $address,
    ];

    return $this->respond($response, 200);
}




   public function addCustomerAddress()
    {
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

        $customerAddressModel = new CustomerAddress($db);

        // Get JSON input

        $data = $this->request->getJSON(true);

        // Validate required fields
         if (!isset($data['customerId']) ||
             !isset($data['mobileNo']) ||
             !isset($data['pincode']) ||
             !isset($data['addressLine1'])
               ) {
             return $this->respond([
            'status' => false,
            'message' => 'Required fields missing: customerId, fullName, mobileNo, pincode, addressLine1, or createdBy',
               ], 400); // Bad Request
             }

        // Format and prepare the data
        $insertData = [
            'customerId'        => $data['customerId'],
            'fullName'          => $data['fullName'],
            'mobileNo'          => $data['mobileNo'],
            'pincode'           => $data['pincode'],
            'addressLine1'      => $data['addressLine1'],
            'addressLine2'      => $data['addressLine2'] ?? '',
            'landmark'          => $data['landmark'] ?? '',
            'city'              => $data['city'] ?? '',
            'state'             => $data['state'] ?? '',
            'country'           => $data['country'] ?? '',
            'deliveryInstruction' => $data['deliveryInstruction'] ?? '',
            'isActive'          => $data['isActive'] ?? 1, // isActive can still be passed from frontend if needed
            'isDeleted'         => 0, // This is a fixed value, keep it
        ];

        // Insert data
        if ($customerAddressModel->insert($insertData)) {
            return $this->respond([
                'status' => true,
                'message' => 'Address added successfully',
                'data' => $insertData
            ], 200);
        } else {
            return $this->respond([
                'status' => false,
                'message' => 'Failed to add address',
                'errors' => $customerAddressModel->errors()
            ], 500);
        }
    }

    public function updateAddress()
    {
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
    
        $customerAddressModel = new CustomerAddress($db);
    
        // Get input data
        $data = $this->request->getJSON(true);

    
        $customerAddressId = $data['customerAddressId'];

    
        // Check if addon exists
        $existingAddress = $customerAddressModel->find($customerAddressId);
        if (!$existingAddress) {
            return $this->respond([
                'status' => false,
                'message' => 'Address not found'
            ], 404);
        }
    
        // Prepare fields to update
        $updateData = [];
    
        if (isset($data['firstName'])) {
            $updateData['firstName'] = $data['firstName'];
        }
        if (isset($data['mobileNo'])) {
            $updateData['mobileNo'] = $data['mobileNo'];
        }
    
        if (isset($data['pincode'])) {
            $updateData['pincode'] = $data['pincode'];
        }
    
        if (isset($data['addressLine1'])) {
            $updateData['addressLine1'] = $data['addressLine1'];
        }
        if (isset($data['addressLine2'])) {
            $updateData['addressLine2'] = $data['addressLine2'];
        }
        if (isset($data['landmark'])) {
            $updateData['landmark'] = $data['landmark'];
        }
        if (isset($data['city'])) {
            $updateData['city'] = $data['city'];
        }
        if (isset($data['state'])) {
            $updateData['state'] = $data['state'];
        }
        if (isset($data['country'])) {
            $updateData['country'] = $data['country'];
        }
        if (isset($data['deliveryInstruction'])) {
            $updateData['deliveryInstruction'] = $data['deliveryInstruction'];
        }
        if (isset($data['customerId'])) {
            $updateData['customerId'] = $data['customerId'];
        }
        if (isset($data['isActive'])) {
            $updateData['isActive'] = $data['isActive'];
        }
    
        if (isset($data['isDeleted'])) {
            $updateData['isDeleted'] = $data['isDeleted'];
        }
        if (isset($data['createdDate'])) {
            $updateData['createdDate'] = date('Y-m-d H:i:s', strtotime($data['createdDate']));
        }
        if (isset($data['modifiedDate'])) {
            $updateData['modifiedDate'] = date('Y-m-d H:i:s', strtotime($data['modifiedDate']));
        }
        if (isset($data['createdBy'])) {
            $updateData['createdBy'] = $data['createdBy'];
        }
        if (isset($data['modifiedBy'])) {
            $updateData['modifiedBy'] = $data['modifiedBy'];
        }
    

    
        // Perform the update
        if ($customerAddressModel->update($customerAddressId, $updateData)) {
            return $this->respond([
                'status' => true,
                'message' => 'Address updated successfully',
                'data' => $updateData
            ], 200);
        } else {
            return $this->respond([
                'status' => false,
                'message' => 'Failed to update address',
                'errors' => $customerAddressModel->errors()
            ], 500);
        }
    }


    public function deleteAddress()
    {
        $input = $this->request->getJSON();
    
        // Validation rules
        $rules = [
            'customerAddressId' => ['rules' => 'required'],
        ];
    
        if ($this->validate($rules)) {
            $tenantService = new TenantService();
            $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
            $model = new CustomerAddress($db);
    
            // Retrieve the address
            $customerAddressId = $input->customerAddressId;
            $address = $model->find($customerAddressId);
    
            if (!$address) {
                return $this->fail(['status' => false, 'message' => 'Address not found'], 404);
            }
    
            // Soft delete
            $updateData = [
                'isDeleted' => 1,
            ];
            $deleted = $model->update($customerAddressId, $updateData);
    
            if ($deleted) {
                return $this->respond(['status' => true, 'message' => 'Address Deleted Successfully'], 200);
            } else {
                return $this->fail(['status' => false, 'message' => 'Failed to delete address'], 500);
            }
        } else {
            // Validation failed
            $response = [
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs',
            ];
            return $this->fail($response, 409);
        }
    }

    public function addDiscount()
{
    try {
        $input = $this->request->getJSON(true);

        // Define validation rules
        $rules = [
            'customerId' => ['rules' => 'required'],
            'disType' => ['rules' => 'required'],
            'value' => ['rules' => 'required'],
        ];

        if (!$this->validate($rules)) {
            return $this->fail([
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs',
            ], 409);
        }


        // Connect to tenant DB
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

        // Insert into customer model
        $model = new CustomerDiscount($db);
        $model->insert($input);

        return $this->respond(['status' => true, 'message' => 'Discount Added Successfully'], 200);

    } catch (\Throwable $e) {
        return $this->fail([
            'status' => false,
            'message' => 'Server Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
}
public function getAllDiscount()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $itemModel = new CustomerDiscount($db);

    // Fetch only addons where isDeleted = 0
    $discount = $itemModel->where('isDeleted', 0)->findAll();

    $response = [
        "status" => true,
        "message" => "All Data Fetched",
        "data" => $discount,
    ];

    return $this->respond($response, 200);
}

public function updateDiscount()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $model = new CustomerDiscount($db);

    // Get input data
    $data = $this->request->getJSON(true);

    if (!isset($data['customerDiscountId'])) {
        return $this->respond([
            'status' => false,
            'message' => 'customerDiscountId is required'
        ], 400);
    }

    $customerDiscountId = $data['customerDiscountId'];

    // Check if addon exists
    $existing = $model->find($customerDiscountId);
    if (!$existing) {
        return $this->respond([
            'status' => false,
            'message' => 'Discount not found'
        ], 404);
    }

    // Prepare fields to update
    $updateData = [];

    if (isset($data['customerId'])) {
        $updateData['customerId'] = $data['customerId'];
    }

    if (isset($data['disType'])) {
        $updateData['disType'] = $data['disType'];
    }

    if (isset($data['value'])) {
        $updateData['value'] = $data['value'];
    }

    if (isset($data['isActive'])) {
        $updateData['isActive'] = $data['isActive'];
    }

    if (isset($data['isDeleted'])) {
        $updateData['isDeleted'] = $data['isDeleted'];
    }

    $updateData['updatedDate'] = date('Y-m-d H:i:s');

    // Perform the update
    if ($model->update($customerDiscountId, $updateData)) {
        return $this->respond([
            'status' => true,
            'message' => 'Discount updated successfully',
            'data' => $updateData
        ], 200);
    } else {
        return $this->respond([
            'status' => false,
            'message' => 'Failed to update discount',
            'errors' => $discount->errors()
        ], 500);
    }
}

public function deleteCustomerDiscount()
    {
        $input = $this->request->getJSON();
    
        // Validation rules
        $rules = [
            'customerDiscountId' => ['rules' => 'required'],
        ];
    
        if ($this->validate($rules)) {
            $tenantService = new TenantService();
            $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
            $model = new CustomerDiscount($db);
    
            // Retrieve the address
            $customerDiscountId = $input->customerDiscountId;
            $discount = $model->find($customerDiscountId);
    
            if (!$discount) {
                return $this->fail(['status' => false, 'message' => 'Id not found'], 404);
            }
    
            // Soft delete
            $updateData = [
                'isDeleted' => 1,
            ];
            $deleted = $model->update($customerDiscountId, $updateData);
    
            if ($deleted) {
                return $this->respond(['status' => true, 'message' => 'Discount  Deleted Successfully'], 200);
            } else {
                return $this->fail(['status' => false, 'message' => 'Failed to delete Discount'], 500);
            }
        } else {
            // Validation failed
            $response = [
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs',
            ];
            return $this->fail($response, 409);
        }
    }

    
}
