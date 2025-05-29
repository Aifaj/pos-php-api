<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ItemModel;
use App\Models\ItemTypeModel;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Addon;
use Config\Database;
use App\Libraries\TenantService;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use App\Models\SlideModel;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;




class Item extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        // Insert the product data into the database
        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
    
        // Load ItemModel with the tenant database connection
        $itemModel = new ItemModel($db);
    
        $items = $itemModel
            ->select('item_mst.*, item_category.itemCategoryName, item_mst.discount,item_mst.discountType, item_category.gstTax')  
            ->join('item_category', 'item_category.itemCategoryId = item_mst.itemCategoryId', 'left')  
            ->where('item_mst.isDeleted', 0)  
            ->findAll();

    
        // Prepare response
        $response = [
            "status" => true,
            "message" => "All Data Fetched",
            "data" => $items,
        ];
    
        return $this->respond($response, 200);
    }
    
    

    public function getAllUnit()
    {
        // Insert the product data into the database
        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
    
        // Load UserModel with the tenant database connection
        $itemModel = new Unit($db);
        $response = [
            "status" => true,
            "message" => "All Data Fetched",
            "data" => $itemModel->findAll(),
        ];
        return $this->respond($response, 200);
    }

public function getalladdons()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $itemModel = new Addon($db);

    // Fetch only addons where isDeleted = 0
    $addons = $itemModel->where('isDeleted', 0)->findAll();

    $response = [
        "status" => true,
        "message" => "All Data Fetched",
        "data" => $addons,
    ];

    return $this->respond($response, 200);
}
    

    public function addAddon()
    {
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

        $addonModel = new Addon($db);

        // Get JSON input
        $data = $this->request->getJSON(true);

        // Validate required fields
        if (!isset($data['addonName'], $data['price'])) {
            return $this->respond([
                'status' => false,
                'message' => 'Required fields missing: addonName, price, or userId',
            ], 400);
        }

        // Format and prepare the data
        $insertData = [
            'addonName'    => $data['addonName'],
            'price'        => $data['price'],
            'isActive'     => $data['isActive'] ?? 1,
            'createdDate'  => date('Y-m-d H:i:s', strtotime($data['createdDate'] ?? 'now'))
        ];

        // Insert data
        if ($addonModel->insert($insertData)) {
            return $this->respond([
                'status' => true,
                'message' => 'Addon added successfully',
                'data' => $insertData
            ], 200);
        } else {
            return $this->respond([
                'status' => false,
                'message' => 'Failed to add addon',
                'errors' => $addonModel->errors()
            ], 500);
        }
    }

    public function updateAddon()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $addonModel = new Addon($db);

    // Get input data
    $data = $this->request->getJSON(true);

    // Validate that addonId is provided
    if (!isset($data['addonId'])) {
        return $this->respond([
            'status' => false,
            'message' => 'addonId is required'
        ], 400);
    }

    $addonId = $data['addonId'];

    // Check if addon exists
    $existingAddon = $addonModel->find($addonId);
    if (!$existingAddon) {
        return $this->respond([
            'status' => false,
            'message' => 'Addon not found'
        ], 404);
    }

    // Prepare fields to update
    $updateData = [];

    if (isset($data['addonName'])) {
        $updateData['addonName'] = $data['addonName'];
    }

    if (isset($data['price'])) {
        $updateData['price'] = $data['price'];
    }

    if (isset($data['isActive'])) {
        $updateData['isActive'] = $data['isActive'];
    }

    if (isset($data['isDeleted'])) {
        $updateData['isDeleted'] = $data['isDeleted'];
    }

    $updateData['updatedDate'] = date('Y-m-d H:i:s');

    // Perform the update
    if ($addonModel->update($addonId, $updateData)) {
        return $this->respond([
            'status' => true,
            'message' => 'Addon updated successfully',
            'data' => $updateData
        ], 200);
    } else {
        return $this->respond([
            'status' => false,
            'message' => 'Failed to update addon',
            'errors' => $addonModel->errors()
        ], 500);
    }
}

    public function getItemsPaging()
    {
        $input = $this->request->getJSON();

        // Get the page number from the input, default to 1 if not provided
        $page = isset($input->page) ? $input->page : 1;
        $perPage = isset($input->perPage) ? $input->perPage : 10;
        $sortField = isset($input->sortField) ? $input->sortField : 'itemId';
        $sortOrder = isset($input->sortOrder) ? $input->sortOrder : 'asc';
        $search = isset($input->search) ? $input->search : '';
        $filter = $input->filter;

        $tenantService = new TenantService();
        
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        // Load StaffModel with the tenant database connection
        $itemModel = new ItemModel($db);

        // Initialize query with 'isDeleted' condition
        $query = $itemModel->where('isDeleted', 0)->where('itemTypeId', $input->itemTypeId); // Apply the deleted check at the beginning

        // Apply search filter for itemName and mrp
        if (!empty($search)) {
            $query->like('itemName', $search)
                ->orLike('mrp', $search);
        }

        // Apply additional filters if provided
        if (!empty($filter)) {
            $filter = json_decode(json_encode($filter), true);
            
            foreach ($filter as $key => $value) {
                if (in_array($key, ['itemName', 'mrp', 'sku'])) {
                    $query->like($key, $value); // LIKE filter for specific fields
                } else if ($key === 'createdDate') {
                    $query->where($key, $value); // Exact match filter for createdDate
                }
            }

           if (!empty($filter['startDate']) && !empty($filter['endDate'])) {
                $startDateTime = $filter['startDate'] . ' 00:00:00';
                $endDateTime = $filter['endDate'] . ' 23:59:59';
                $query->where('createdDate >=', $startDateTime)
                    ->where('createdDate <=', $endDateTime);
            } else if (!empty($filter['dateRange'])) {
                if ($filter['dateRange'] === 'last7days') {
                    $last7DaysStart = date('Y-m-d 00:00:00', strtotime('-7 days'));
                    $query->where('createdDate >=', $last7DaysStart);
                } else if ($filter['dateRange'] === 'last30days') {
                    $last30DaysStart = date('Y-m-d 00:00:00', strtotime('-30 days'));
                    $query->where('createdDate >=', $last30DaysStart);
                }
            }

        }

        // Apply sorting
        $query->orderBy('itemId', 'desc');

        // Execute the query with pagination
        $item = $query->paginate($perPage, 'default', $page);

        // Get pagination data
        $pager = $itemModel->pager;

        // Prepare the response
        $response = [
            "status" => true,
            "message" => "All item Data Fetched",
            "data" => $item,
            "pagination" => [
                "currentPage" => $pager->getCurrentPage(),
                "totalPages" => $pager->getPageCount(),
                "totalItems" => $pager->getTotal(),
                "perPage" => $perPage
            ]
        ];

        return $this->respond($response, 200);
    }

    // public function create()
    // {
    //     // Retrieve the input data from the request
    //     $input = $this->request->getPost();
        
    //     // Define validation rules for required fields
    //     $rules = [
    //         'itemName' => ['rules' => 'required']
    //     ];

    //     if ($this->validate($rules)) {
    //         $key = "Exiaa@11";
    //         $header = $this->request->getHeader("Authorization");
    //         $token = null;
    
    //         // extract the token from the header
    //         if(!empty($header)) {
    //             if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
    //                 $token = $matches[1];
    //             }
    //         }
            
    //         $decoded = JWT::decode($token, new Key($key, 'HS256'));
    //                   // Handle cover image update as base64
    //                   if (isset($input['coverImage']) && !empty($input['coverImage'])) {
    //                     $coverImageData = base64_decode(preg_replace('#^data:image/png;base64,#i', '', $input['coverImage']));
    
    //                     // Handle cover image upload
    //                     $key = "Exiaa@11";
    //                     $header = $this->request->getHeader("Authorization");
    //                     $token = null;
    
    //                     // Extract the token from the header
    //                     if (!empty($header)) {
    //                         if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
    //                             $token = $matches[1];
    //                         }
    //                     }
    
    //                     $decoded = JWT::decode($token, new Key($key, 'HS256'));
    //                     $coverImagePath = FCPATH . 'uploads/' . $decoded->tenantName . '/itemImages/';
    
    //                     if (!is_dir($coverImagePath)) {
    //                         mkdir($coverImagePath, 0777, true);
    //                     }
    
    //                     $coverImageName = uniqid() . '.png'; // Ensure the file extension is .png
    //                     file_put_contents($coverImagePath . $coverImageName, $coverImageData);
    
    //                     $input['coverImage'] = $decoded->tenantName . '/itemImages/' . $coverImageName;
    //                     $updateData['coverImage'] = $input['coverImage'];
    //                 }
    
    //                 // Handle product image update as base64
    //                 if (isset($input['productImages']) && !empty($input['productImages'])) {
    //                     // Split the base64 images
    //                     $base64Images = explode(',', $input['productImages']);
    //                     $imageUrls = [];
            
    //                     // Process each image in the array
    //                     foreach ($base64Images as $index => $base64Image) {
    //                         // Only process if the image exists
    //                         if (empty($base64Image)) {
    //                             continue;
    //                         }
            
    //                         $imageData = base64_decode(preg_replace('#^data:image/png;base64,#i', '', $base64Image));
    //                         $imageName = uniqid() . '.png'; // Ensure the file extension is .png
            
    //                         // Handle product image upload
    //                         $key = "Exiaa@11";
    //                         $header = $this->request->getHeader("Authorization");
    //                         $token = null;
            
    //                         // Extract the token from the header
    //                         if (!empty($header)) {
    //                             if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
    //                                 $token = $matches[1];
    //                             }
    //                         }
            
    //                         $decoded = JWT::decode($token, new Key($key, 'HS256'));
    //                         $productImagePath = FCPATH . 'uploads/' . $decoded->tenantName . '/itemSlideImages/';
            
    //                         if (!is_dir($productImagePath)) {
    //                             mkdir($productImagePath, 0777, true);
    //                         }
            
    //                         file_put_contents($productImagePath . $imageName, $imageData);
    //                         $imageUrls[] = $decoded->tenantName . '/itemSlideImages/' . $imageName;
    //                     }
            
    //                     // Only update the product images if we have valid image URLs
    //                     if (count($imageUrls) > 0) {
    //                         $input['productImages'] = implode(',', $imageUrls);
    //                         $updateData['productImages'] = $input['productImages'];
    //                     }
    //                 }
        

    //         // Insert the product data into the database
    //         $tenantService = new TenantService();
    //         // Connect to the tenant's database
    //         $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
    //         $model = new ItemModel($db);
    //         $model->insert($input);

    //         return $this->respond(['status' => true, 'message' => 'Item Added Successfully'], 200);
    //     } else {
    //         // If validation fails, return the error messages
    //         $response = [
    //             'status' => false,
    //             'errors' => $this->validator->getErrors(),
    //             'message' => 'Invalid Inputs',
    //         ];
    //         return $this->fail($response, 409);
    //     }
    // }

   public function create()
{
    $input = $this->request->getPost();

    $rules = [
        'itemName' => ['rules' => 'required']
    ];

    if ($this->validate($rules)) {

        $tenantName = $input['tenantName'];

        // Handle cover image
        if (!empty($input['coverImage'])) {
            $coverImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $input['coverImage']));
            $coverImagePath = FCPATH . 'uploads/' . $tenantName . '/itemImages/';

            if (!is_dir($coverImagePath)) {
                mkdir($coverImagePath, 0777, true);
            }

            $coverImageName = uniqid() . '.png';
            file_put_contents($coverImagePath . $coverImageName, $coverImageData);

            $input['coverImage'] = $tenantName . '/itemImages/' . $coverImageName;
            $updateData['coverImage'] = $input['coverImage'];
        }

        // ✅ Correctly handle JSON-encoded base64 images
        if (!empty($input['productImages'])) {
            $imageUrls = [];

            $decodedImages = json_decode($input['productImages'], true); // ✅ decode JSON array

            if (is_array($decodedImages)) {
                foreach ($decodedImages as $base64Image) {
                    if (empty($base64Image)) continue;

                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                    $imageName = uniqid() . '.png';
                    $productImagePath = FCPATH . 'uploads/' . $tenantName . '/itemSlideImages/';

                    if (!is_dir($productImagePath)) {
                        mkdir($productImagePath, 0777, true);
                    }

                    file_put_contents($productImagePath . $imageName, $imageData);
                    $imageUrls[] = $tenantName . '/itemSlideImages/' . $imageName;
                }

                if (count($imageUrls) > 0) {
                    $input['productImages'] = implode(',', $imageUrls);
                    $updateData['productImages'] = $input['productImages'];
                }
            }
        }

        // DB insert
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new ItemModel($db);
        $model->insert($input);

        return $this->respond(['status' => true, 'message' => 'Item Added Successfully'], 200);

    } else {
        return $this->fail([
            'status' => false,
            'errors' => $this->validator->getErrors(),
            'message' => 'Invalid Inputs',
        ], 409);
    }
}






public function update()
{
    try {
        $input = $this->request->getPost();

        $rules = [
            'itemId' => ['rules' => 'required|numeric'],
        ];

        if ($this->validate($rules)) {

            $tenantName = $input['tenantName'];

            $tenantService = new TenantService();
            $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
            $model = new ItemModel($db);

            $itemId = $input['itemId'];
            $item = $model->find($itemId);

            if (!$item) {
                return $this->fail(['status' => false, 'message' => 'Item not found'], 404);
            }

            $updateData = [
                'itemName' => $input['itemName'] ?? '',
                'itemCategoryId' => $input['itemCategoryId'] ?? '',
                'mrp' => $input['mrp'] ?? '',
                'discountType' => $input['discountType'] ?? '',
                'discount' => $input['discount'] ?? '',
                'barcode' => $input['barcode'] ?? '',
                'description' => $input['description'] ?? '',
                'itemTypeId' => $input['itemTypeId'] ?? '',
                'sku' => $input['sku'] ?? '',
                'hsnCode' => $input['hsnCode'] ?? '',
                'feature' => $input['feature'] ?? '',
                'unitName' => $input['unitName'] ?? '',
                'isAddons' => $input['isAddons'] ?? '',
                'addons' => $input['addons'] ?? '',
                'isPortion' => $input['isPortion'] ?? '',
                'portion' => $input['portion'] ?? '',
            ];

            // Save cover image
            if (!empty($input['coverImage'])) {
                $coverImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $input['coverImage']));
                $coverImagePath = FCPATH . 'uploads/' . $tenantName . '/itemImages/';

                if (!is_dir($coverImagePath)) {
                    mkdir($coverImagePath, 0777, true);
                }

                $coverImageName = uniqid() . '.png';
                file_put_contents($coverImagePath . $coverImageName, $coverImageData);

                $updateData['coverImage'] = $tenantName . '/itemImages/' . $coverImageName;
            }

            // Save gallery images (append new images to existing without duplicates)
           if (!empty($input['productImages'])) {
    // Extract all full base64 image strings using regex
    preg_match_all('/data:image\/[a-zA-Z]+;base64,[^"]+/', $input['productImages'], $matches);
    $base64Images = $matches[0] ?? [];
    $imageUrls = [];

    foreach ($base64Images as $base64Image) {
        $base64Image = trim($base64Image);
        if (empty($base64Image)) continue;

        $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
        $imageHash = md5($imageData);
        $imageName = $imageHash . '.png';
        $productImagePath = FCPATH . 'uploads/' . $tenantName . '/itemSlideImages/';

        if (!is_dir($productImagePath)) {
            mkdir($productImagePath, 0777, true);
        }

        $fullImagePath = $productImagePath . $imageName;

        if (!file_exists($fullImagePath)) {
            file_put_contents($fullImagePath, base64_decode($imageData));
        }

        $imageUrls[] = $tenantName . '/itemSlideImages/' . $imageName;
    }

    if (count($imageUrls) > 0) {
        $existingImages = array_filter(array_map('trim', explode(',', $item['productImages'] ?? '')));
        $newImages = array_filter(array_map('trim', $imageUrls));
        $mergedImages = array_unique(array_merge($existingImages, $newImages));

        $updateData['productImages'] = implode(',', $mergedImages);
    }
}
            if ($model->update($itemId, $updateData)) {
                return $this->respond(['status' => true, 'message' => 'Item Updated Successfully'], 200);
            } else {
                return $this->fail(['status' => false, 'message' => 'Failed to update item'], 500);
            }
        } else {
            return $this->fail([
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ], 409);
        }
    } catch (\Throwable $e) {
        return $this->fail([
            'status' => false,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
}



    
    public function delete()
    {
        $input = $this->request->getJSON();
        
        // Validation rules for the course
        $rules = [
            'itemId' => ['rules' => 'required'], // Ensure eventId is provided and is numeric
        ];

        // Validate the input
        if ($this->validate($rules)) {
           
            // Insert the product data into the database
            $tenantService = new TenantService();
            // Connect to the tenant's database
            $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
            $model = new ItemModel($db);

            // Retrieve the course by eventId
            $itemId = $input->itemId;
            $item = $model->find($itemId); // Assuming find method retrieves the course

            if (!$item) {
                return $this->fail(['status' => false, 'message' => 'Course not found'], 404);
            }

            $updateData = [
                'isDeleted' => 1,
            ];
            $deleted = $model->update($itemId, $updateData);

            if ($deleted) {
                return $this->respond(['status' => true, 'message' => 'Item Deleted Successfully'], 200);
            } else {
                return $this->fail(['status' => false, 'message' => 'Failed to delete course'], 500);
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

    public function getAllItemCategory()
    {
        // 🧪 Debug logs
        log_message('error', 'Inside getAllItemCategory function');
    
        $header = $this->request->getHeaderLine('X-Tenant-Config');
        log_message('error', 'Tenant Header: ' . $header);
    
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($header);
    
        if (!$db) {
            log_message('error', 'ERROR: Database config is NULL');
            return $this->respond(['status' => false, 'message' => 'Database config error'], 500);
        }
    
        try {
            $model = new ItemCategory($db);
            $itemCategories = $model->where('isDeleted', 0)->findAll();
            return $this->respond(['status' => true, 'message' => 'Data fetched successfully', 'data' => $itemCategories], 200);
        } catch (\Throwable $e) {
            log_message('error', 'Exception: ' . $e->getMessage());
            return $this->respond(['status' => false, 'message' => 'Internal Server Error'], 500);
        }
    }
    

public function createCategory()
{
    // Retrieve normal input fields
    $input = $this->request->getPost();

    // Define validation rules
    $rules = [
        'itemCategoryName' => ['rules' => 'required'],
    ];

    if ($this->validate($rules)) {
        // Extract tenant name from input
        $tenantName = $input['tenantName'] ?? 'defaultTenant';

        // Handle binary file upload
        $coverImage = $this->request->getFile('coverImage');
        if ($coverImage && $coverImage->isValid() && !$coverImage->hasMoved()) {
            // Generate image name
            $coverImageName = $coverImage->getRandomName();

            // Create folder if not exists
            $coverImagePath = FCPATH . 'uploads/' . $tenantName . '/itemCategoryImages/';
            if (!is_dir($coverImagePath)) {
                mkdir($coverImagePath, 0777, true);
            }

            // Move uploaded file
            $coverImage->move($coverImagePath, $coverImageName);

            // Save relative path in DB
            $input['coverImage'] = $tenantName . '/itemCategoryImages/' . $coverImageName;
        }

        // Connect to the tenant database
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new ItemCategory($db);

        // Save to database
        $itemCategory = $model->insert($input);

        return $this->respond([
            "status" => true,
            "message" => "Item Category Added Successfully",
            "data" => $itemCategory
        ], 200);
    } else {
        return $this->fail([
            'status' => false,
            'errors' => $this->validator->getErrors(),
            'message' => 'Invalid Inputs'
        ], 409);
    }
}

   public function updateCategory()
{
    try {
        $input = $this->request->getPost();

        $rules = [
            'itemCategoryId' => ['rules' => 'required|numeric'],
            'itemCategoryName' => ['rules' => 'required']
        ];

        if ($this->validate($rules)) {
            $tenantName = $input['tenantName'] ?? 'defaultTenant';

            // Get tenant DB connection
            $tenantService = new TenantService();
            $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

            $model = new ItemCategory($db);
            $itemCategoryId = $input['itemCategoryId'];

            $existingItem = $model->find($itemCategoryId);
            if (!$existingItem) {
                return $this->fail(['status' => false, 'message' => 'Item not found'], 404);
            }

            $updateData = [
                'itemCategoryName' => $input['itemCategoryName'] ?? $existingItem['itemCategoryName'],
                'description'      => $input['description'] ?? $existingItem['description'],
            ];

            $coverImage = $this->request->getFile('coverImage');
            if ($coverImage && $coverImage->isValid() && !$coverImage->hasMoved()) {
                $coverImageName = $coverImage->getRandomName();
                $coverImagePath = FCPATH . 'uploads/' . $tenantName . '/itemCategoryImages/';

                if (!is_dir($coverImagePath)) {
                    mkdir($coverImagePath, 0777, true);
                }

                $coverImage->move($coverImagePath, $coverImageName);
                $updateData['coverImage'] = $tenantName . '/itemCategoryImages/' . $coverImageName;
            }

            $updated = $model->update($itemCategoryId, $updateData);

            if ($updated) {
                return $this->respond(['status' => true, 'message' => 'Item Category Updated Successfully'], 200);
            } else {
                echo "<pre>Update failed: ";
                print_r($db->error());
                echo "</pre>";
                exit;
            }
        } else {
            echo "<pre>Validation Failed:\n";
            print_r($this->validator->getErrors());
            echo "</pre>";
            exit;
        }
    } catch (\Throwable $e) {
        echo "<pre>Caught Exception:\n";
        print_r($e->getMessage());
        echo "\nFile: " . $e->getFile();
        echo "\nLine: " . $e->getLine();
        echo "\nTrace:\n" . $e->getTraceAsString();
        echo "</pre>";
        exit;
    }
}


    public function deleteCategory()
    {
        $input = $this->request->getJSON();
        
        // Validation rules for the course
        $rules = [
            'itemCategoryId' => ['rules' => 'required'], // Ensure eventId is provided and is numeric
        ];

        // Validate the input
        if ($this->validate($rules)) {
           
            // Insert the product data into the database
            $tenantService = new TenantService();
            // Connect to the tenant's database
            $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
            $model = new ItemCategory($db);

            // Retrieve the course by eventId
            $itemCategoryId = $input->itemCategoryId;
            $item = $model->find($itemCategoryId); // Assuming find method retrieves the course

            if (!$item) {
                return $this->fail(['status' => false, 'message' => 'Course not found'], 404);
            }

            $updateData = [
                'isDeleted' => 1,
            ];
            $deleted = $model->update($itemCategoryId, $updateData);

            if ($deleted) {
                return $this->respond(['status' => true, 'message' => 'Item Deleted Successfully'], 200);
            } else {
                return $this->fail(['status' => false, 'message' => 'Failed to delete course'], 500);
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

    public function getAllCategoryWeb()
    {
        $input = $this->request->getJSON();

         // Insert the product data into the database
         $tenantService = new TenantService();
         // Connect to the tenant's database
         $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new ItemCategory($db);
        $itemCategories = $model->findAll();
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $itemCategories], 200);
    }

    public function getAllItemByCategoryWeb()
    {
        // Retrieve categoryId from URI segment
        $categoryId = $this->request->getUri()->getSegment(1);
        
        if (!$categoryId) {
            return $this->respond(["status" => false, "message" => "Category ID not provided."], 400);
        }
    
         // Insert the product data into the database
         $tenantService = new TenantService();
         // Connect to the tenant's database
         $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
    
        // Load ItemCategory model with the tenant database connection
        $category = new ItemCategory($db);
        $categories = $category->findAll(); // This loads all categories
    
        // Load ItemModel with the tenant database connection
        $model = new ItemModel($db);
    
        // Fetch items by category ID
        try {
            // Use the where method directly
            $items = $model->where('itemCategoryId', $categoryId)->findAll();
        } catch (\Exception $e) {
            return $this->respond(["status" => false, "message" => "Failed to fetch items: " . $e->getMessage()], 500);
        }
    
        // Return the response
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $items], 200);
    }

    public function getItemByItemTypeId($itemTypeId){

        // Insert the product data into the database
        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new ItemModel($db);
        $items = $model->where('itemTypeId', $itemTypeId)->findAll();
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $items], 200);

    }

    public function getAllItemByTagWeb()
    {
        $tag = $this->request->getSegment(1);

         // Insert the product data into the database
         $tenantService = new TenantService();
         // Connect to the tenant's database
         $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

        // Load UserModel with the tenant database connection
        $model = new ItemModel($db);
        $items = $model->findAllByTag($tag);
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $items], 200);
    }

   

    public function getFourItemProductByCategory()
    {
        try {
            $tenantService = new TenantService();
            $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
    
            $categoryModel = new ItemCategory($db);
            $categories = $categoryModel->where('itemTypeId', 3)->where('isDeleted', 0)->where('isActive', 1)->findAll(); // Fetch all categories')->where('isActive', 1)->where('isDeleted', 0)->findAll(); // Fetch all categories

            if (empty($categories)) {
                return $this->respond(["status" => false, "message" => "No categories found"], 404);
            }

            $categoryList = array();

            foreach ($categories as $category) {;
                $category['items'] = []; // Initialize items array for each category
    
                $itemModel = new ItemModel($db);
                // Fetch 4 random items by category ID that are not deleted
                $items = $itemModel->where('itemCategoryId', $category['itemCategoryId'])
                ->where('isActive', 1)
                ->where('isDeleted', 0)
                ->orderBy('RAND()') // Random order
                ->findAll(4); // Limit to 4 items
                $category['items'] = $items;
                array_push($categoryList, $category);
            }

            return $this->respond(["status" => true, "message" => "Items fetched successfully", "data" => $categoryList], 200);
        } catch (\Exception $e) {
            return $this->failServerError("Server Error: " . $e->getMessage());
        }
    }
    

    public function show()
    {
        $input = $this->request->getJSON();
        $itemId = $input->itemId;
        log_message('error', 'Item Id: ' . $itemId);
        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new ItemModel($db);
        $item = $model->find($itemId);
        $category = new ItemCategory($db);
        $item['category'] = $category->find($item['itemCategoryId']);
        if (!$item) {
            return $this->respond(["status" => false, "message" => "Item not found"], 404);
        }
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $item], 200);
    }

    public function filteredItems()
    {
        $input = $this->request->getJSON();

        // Check if required fields exist in the input, if not, set them to a default value or handle the error
        $categories = isset($input->selectedCategoryIds) ? (is_array($input->selectedCategoryIds) ? $input->selectedCategoryIds : explode(',', $input->selectedCategoryIds)) : [];
        $brands = isset($input->brands) ? (is_array($input->brands) ? $input->brands : explode(',', $input->brands)) : [];
        $minPrice = isset($input->minPrice) ? $input->minPrice : null;
        $maxPrice = isset($input->maxPrice) ? $input->maxPrice : null;
        
        // Pagination parameters
        $page = isset($input->page) ? (int)$input->page : 1;  // Default to page 1 if not provided
        $limit = isset($input->limit) ? (int)$input->limit : 30;  // Default to 10 items per page if not provided
        
        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new ItemModel($db);

        $model = $model->where('isDeleted', 0)->where('itemTypeId', $input->itemTypeId);
        // Get filtered items with pagination
        $items = $model->getFilteredItems($categories, $brands, $minPrice, $maxPrice, $page, $limit);
        
        // Get the total count of items for pagination info
        $totalItems = $model->getFilteredItemsCount($categories, $brands, $minPrice, $maxPrice);
        
        // Calculate total pages
        $totalPages = ceil($totalItems / $limit);
        
        // Return paginated response
        return $this->respond([
            "status" => true,
            "message" => "All Data Fetched",
            "data" => $items,
            "pagination" => [
                "currentPage" => $page,
                "totalPages" => $totalPages,
                "totalItems" => $totalItems,
                "limit" => $limit
            ]
        ], 200);
        

    }

    public function getall()
    {
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $slideModel = new SlideModel($db);
        
        $slides = $slideModel->findAll();
        return $this->respond(["status" => true, "message" => "All Slides Fetched", "data" => $slides], 200);
    }

    public function deleteItem($id)
    {
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new ItemModel($db);

        // Check if item exists
        $item = $model->find($id);
        if (!$item) {
            return $this->respond([
                'status' => false,
                'message' => 'Item not found.'
            ], 404);
        }

        // Soft delete the item (set isDeleted = 1)
        $model->update($id, ['isDeleted' => 1]);

        return $this->respond([
            'status' => true,
            'message' => 'Item deleted successfully.'
        ], 200);
    }


    public function createProductCategory()
    {
        try {
            $input = $this->request->getJSON(true);
    
            // Define validation rules
            $rules = [
                'productCategoryName' => ['rules' => 'required'],
                'description' => ['rules' => 'required'],
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
            $model = new ProductCategory($db);
            $model->insert($input);
    
            return $this->respond(['status' => true, 'message' => 'Category Added Successfully'], 200);
    
        } catch (\Throwable $e) {
            return $this->fail([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    


public function getAllProductCategory()
{
    // 🧪 Debug logs
    log_message('error', 'Inside getAllProductCategory function');

    $header = $this->request->getHeaderLine('X-Tenant-Config');
    log_message('error', 'Tenant Header: ' . $header);

    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($header);

    if (!$db) {
        log_message('error', 'ERROR: Database config is NULL');
        return $this->respond(['status' => false, 'message' => 'Database config error'], 500);
    }

    try {
        $model = new ProductCategory($db);
        $productCategory = $model->where('isDeleted', 0)->findAll();
        return $this->respond(['status' => true, 'message' => 'Data fetched successfully', 'data' => $productCategory], 200);
    } catch (\Throwable $e) {
        log_message('error', 'Exception: ' . $e->getMessage());
        return $this->respond(['status' => false, 'message' => 'Internal Server Error'], 500);
    }
}

public function updateProductCategory()
{
    try {
        $input = $this->request->getJSON(true); // true = return associative array

        $rules = [
            'productCategoryName' => ['rules' => 'required'],
            'description' => ['rules' => 'required'],
        ];

        if ($this->validate($rules)) {
            $tenantName = $input['tenantName'] ?? 'defaultTenant';

            // Get tenant DB connection
            $tenantService = new TenantService();
            $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

            $model = new ProductCategory($db);

            if (!isset($input['productCategoryId'])) {
                return $this->fail(['status' => false, 'message' => 'productCategoryId is required'], 400);
            }
            $productCategoryId = $input['productCategoryId'];

            $existingItem = $model->find($productCategoryId);
            if (!$existingItem) {
                return $this->fail(['status' => false, 'message' => 'Item not found'], 404);
            }

            $updateData = [
                'productCategoryName' => $input['productCategoryName'] ?? $existingItem['productCategoryName'],
                'description'      => $input['description'] ?? $existingItem['description'],
                'isDeleted'      => $input['isDeleted'] ?? $existingItem['isDeleted'],

            ];

           

            $updated = $model->update($productCategoryId, $updateData);

            if ($updated) {
                return $this->respond(['status' => true, 'message' => 'Product Category Updated Successfully'], 200);
            } else {
                echo "<pre>Update failed: ";
                print_r($db->error());
                echo "</pre>";
                exit;
            }
        } else {
            echo "<pre>Validation Failed:\n";
            print_r($this->validator->getErrors());
            echo "</pre>";
            exit;
        }
    } catch (\Throwable $e) {
        echo "<pre>Caught Exception:\n";
        print_r($e->getMessage());
        echo "\nFile: " . $e->getFile();
        echo "\nLine: " . $e->getLine();
        echo "\nTrace:\n" . $e->getTraceAsString();
        echo "</pre>";
        exit;
    }
}

public function createProductSubCategory()
    {
        try {
            $input = $this->request->getJSON(true);
    
            // Define validation rules
            $rules = [
                'productSubCategoryName' => ['rules' => 'required'],
                'productCategoryId' => ['rules' => 'required'],
                'description' => ['rules' => 'required'],
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
            $model = new ProductSubCategory($db);
            $model->insert($input);
    
            return $this->respond(['status' => true, 'message' => ' SubCategory Added Successfully'], 200);
    
        } catch (\Throwable $e) {
            return $this->fail([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function getAllSubProductCategory()
{
    // 🧪 Debug logs
    log_message('error', 'Inside getAllProductCategory function');

    $header = $this->request->getHeaderLine('X-Tenant-Config');
    log_message('error', 'Tenant Header: ' . $header);

    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($header);

    if (!$db) {
        log_message('error', 'ERROR: Database config is NULL');
        return $this->respond(['status' => false, 'message' => 'Database config error'], 500);
    }

    try {
        $model = new ProductSubCategory($db);
        $productSubCategory = $model->where('isDeleted', 0)->findAll();
        return $this->respond(['status' => true, 'message' => 'Data fetched successfully', 'data' => $productSubCategory], 200);
    } catch (\Throwable $e) {
        log_message('error', 'Exception: ' . $e->getMessage());
        return $this->respond(['status' => false, 'message' => 'Internal Server Error'], 500);
    }
}

public function updateSubProductCategory()
{
    try {
        $input = $this->request->getJSON(true); // true = return associative array

        $rules = [
            'productSubCategoryName' => ['rules' => 'required'],
            'productCategoryId' => ['rules' => 'required'],
            'description' => ['rules' => 'required'],
        ];

        if ($this->validate($rules)) {
            $tenantName = $input['tenantName'] ?? 'defaultTenant';

            // Get tenant DB connection
            $tenantService = new TenantService();
            $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

            $model = new ProductSubCategory($db);

            if (!isset($input['productSubCategoryId'])) {
                return $this->fail(['status' => false, 'message' => 'productCategoryId is required'], 400);
            }
            $productSubCategoryId = $input['productSubCategoryId'];

            $existingItem = $model->find($productSubCategoryId);
            if (!$existingItem) {
                return $this->fail(['status' => false, 'message' => 'Item not found'], 404);
            }

            $updateData = [

                'productSubCategoryName' => $input['productSubCategoryName'] ?? $existingItem['productSubCategoryName'],
                'productCategoryId' => $input['productCategoryId'] ?? $existingItem['productCategoryId'],
                'description'      => $input['description'] ?? $existingItem['description'],
                'isDeleted'      => $input['isDeleted'] ?? $existingItem['isDeleted'],

            ];

           

            $updated = $model->update($productSubCategoryId, $updateData);

            if ($updated) {
                return $this->respond(['status' => true, 'message' => 'Product SubCategory Updated Successfully'], 200);
            } else {
                echo "<pre>Update failed: ";
                print_r($db->error());
                echo "</pre>";
                exit;
            }
        } else {
            echo "<pre>Validation Failed:\n";
            print_r($this->validator->getErrors());
            echo "</pre>";
            exit;
        }
    } catch (\Throwable $e) {
        echo "<pre>Caught Exception:\n";
        print_r($e->getMessage());
        echo "\nFile: " . $e->getFile();
        echo "\nLine: " . $e->getLine();
        echo "\nTrace:\n" . $e->getTraceAsString();
        echo "</pre>";
        exit;
    }
}

}
