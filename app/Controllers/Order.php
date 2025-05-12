<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\ItemModel;
use App\Libraries\TenantService;

use Config\Database;

class Order extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $tenantService = new TenantService();

        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

        // Load UserModel with the tenant database connection
        $OrderModel = new OrderModel($db);
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $OrderModel->findAll()], 200);
    }




public function getOrdersPaging()
{
    $input = $this->request->getJSON();

    // Get the page number from the input, default to 1 if not provided
    $page = isset($input->page) ? $input->page : 1;
    $perPage = isset($input->perPage) ? $input->perPage : 10;
    $sortField = isset($input->sortField) ? $input->sortField : 'orderId';
    $sortOrder = isset($input->sortOrder) ? $input->sortOrder : 'asc';
    $search = isset($input->search) ? $input->search : '';
    $filter = $input->filter;

    $tenantService = new TenantService();
    
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
    
    // Load the models with the tenant database connection
    $OrderModel = new OrderModel($db);
    $OrderDetailModel = new OrderDetailModel($db);
    $ItemModel = new ItemModel($db);  // Assuming ItemModel exists

    // Base query for Orders
    $query = $OrderModel;

    // Apply filters based on user input
    if (!empty($filter)) {
        $filter = json_decode(json_encode($filter), true);

        foreach ($filter as $key => $value) {
            if (in_array($key, ['orderNo', 'orderDate',  'businessNameFor'])) {
                $query->like($key, $value); // LIKE filter for specific fields
            } else if (in_array($key, ['createdDate'])) {
                $query->where($key, $value); // Exact match filter
            }
        }

        // Apply Date Range Filter
        if (!empty($filter['startDate']) && !empty($filter['endDate'])) {
            $query->where('createdDate >=', $filter['startDate'])
                ->where('createdDate <=', $filter['endDate']);
        }
    }

    $query->where('isDeleted', 0);

    // Apply Sorting
    if (!empty($sortField) && in_array(strtoupper($sortOrder), ['ASC', 'DESC'])) {
        $query->orderBy($sortField, $sortOrder);
    }

    // Fetch all Orders
    $orders = $query->paginate($perPage, 'default', $page);
    $pager = $OrderModel->pager;

    // Fetch details for each orders and merge with the main orders data
    foreach ($orders as &$order) {
        // Fetch related orders details for each ordersId
        $ordersDetails = $OrderDetailModel->where('orderId', $order['orderId'])->findAll();

        // Merge the details under 'items', and for each orders detail, fetch the corresponding item data
        foreach ($ordersDetails as &$ordersDetail) {
            // Fetch item data using itemId from ItemModel
            $item = $ItemModel->find($ordersDetail['itemId']);  // Assuming 'itemId' is a field in OrderDetailModel
            
            // Merge item data into ordersDetail
            if ($item) {
                $ordersDetail['item'] = $item;  // Now each ordersDetail will have item details merged into it
            }
        }

        // Add the orders details under 'items'
        $order['items'] = $ordersDetails;
    }

    // Prepare the response
    $response = [
        "status" => true,
        "message" => "All Order Data Fetched",
        "data" => $orders,
        "pagination" => [
            "currentPage" => $pager->getCurrentPage(),
            "totalPages" => $pager->getPageCount(),
            "totalItems" => $pager->getTotal(),
            "perPage" => $perPage
        ]
    ];

    return $this->respond($response, 200);
}


    public function getOrdersWebsite()
    {
       
        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        // Load UserModel with the tenant database connection
        $OrderModel = new OrderModel($db);
        $Order = $OrderModel->orderBy('createdDate', 'DESC')->where('isActive', 1)->where('isDeleted', 0)->findAll();
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $OrderModel], 200);
    }

 

    public function create()
{
    $input = $this->request->getJSON();

    // Validation rules for order
    $rules = [
        'orderNo' => ['rules' => 'required'],
        'orderDate' => ['rules' => 'required'],
    ];

    // Validate form data
    if ($this->validate($rules)) {
       
        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new OrderModel($db);

        // Insert the order into the 'order' table
        $orderData = [
            'orderNo' => $input->orderNo,
            'orderDate' => $input->orderDate,
            'businessNameFor' => $input->businessNameFor,
            'email' => $input->email,
            'mobileNo' => $input->mobileNo,
            'address'=> $input->address,
            'total'=> $input->total,
            'totalItem'=> $input->totalItems,
            'totalPrice'=> $input->totalPrice,

        ];

        // Insert the order and retrieve the generated orderId
        $orderId= $model->insert($orderData);

        if ($orderId) {
            // Now insert the items into the item_details table using the orderId

            $itemDetailsModel = new OrderDetailModel($db); // Assuming you have this model for the item details

            // Iterate through each item in the input and insert into item_details
            foreach ($input->items as $item) {
                $itemData = [
                    'orderId' => $orderId,  // Foreign key linking to the quotation
                    'itemId' => $item->itemId,
                    'quantity' => $item->quantity,
                    'rate' => $item->rate,
                    'amount' => $item->amount
                ];
                
                // Insert the item into the item_details table
                 $itemDetailsModel->insert($itemData);  // Assuming insert() method returns the inserted item ID
                 
            }

            return $this->respond(['status' => true, 'message' => 'Order and items added successfully'], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Failed to create the Order'], 500);
        }
    } else {
        // Return validation errors
        $response = [
            'status' => false,
            'errors' => $this->validator->getErrors(),
            'message' => 'Invalid Inputs'
        ];
        return $this->fail($response, 409);
    }
}


    // public function update()
    // {
    //     $input = $this->request->getJSON();
        
    //     // Validation rules for the customer
    //     $rules = [
    //         'orderId' => ['rules' => 'required|numeric'], // Ensure customerId is provided and is numeric
    //     ];

    //     // Validate the input
    //     if ($this->validate($rules)) {
           
    //     $tenantService = new TenantService();
    //     // Connect to the tenant's database
    //       $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
    //         $model = new OrderModel($db);

    //         // Retrieve the customer by customerId
    //         $orderId = $input->orderId;
    //         $order = $model->find($orderId); // Assuming find method retrieves the customer

    //         if (!$order) {
    //             return $this->fail(['status' => false, 'message' => 'order not found'], 404);
    //         }

    //         // Prepare the data to be updated (exclude customerId if it's included)
    //         $updateData = [
    //             'orderNo' => $input->orderNo,
    //             'orderDate' => $input->orderDate,
    //             'businessNameFor' => $input->businessNameFor,
    //             'email' => $input->email,
    //             'mobileNo' => $input->mobileNo,
    //             'address'=> $input->address,

    //         ];

    //         // Update the customer with new data
    //         $updated = $model->update($orderId, $updateData);

    //         if ($updated) {
    //             return $this->respond(['status' => true, 'message' => 'order Updated Successfully'], 200);
    //         } else {
    //             return $this->fail(['status' => false, 'message' => 'Failed to update order'], 500);
    //         }
    //     } else {
    //         // Validation failed
    //         $response = [
    //             'status' => false,
    //             'errors' => $this->validator->getErrors(),
    //             'message' => 'Invalid Inputs'
    //         ];
    //         return $this->fail($response, 409);
    //     }
    // }
    public function update()
    {
        // Get input data from the request body
        $input = $this->request->getJSON();

        // Validation rules for the order and Quotation Details
        $rules = [
            'orderId' => ['rules' => 'required|numeric'], // Ensure orderId is provided and is numeric
        ];

        // Validate the input
        if (!$this->validate($rules)) {
            // Validation failed
            $response = [
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
            return $this->fail($response, 409);
        }

        // Get tenant database configuration
        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

        // Start a database transaction
        $db->transBegin();

        // Instantiate the models
        $OrderModel = new OrderModel($db);
        $OrderDetailModel = new OrderDetailModel($db);

        // Retrieve the order by orderId
        $orderId = $input->orderId;
        $order = $OrderModel->find($orderId); // Assuming find method retrieves the order by orderId

        if (!$order) {
            $db->transRollback();
            return $this->fail(['status' => false, 'message' => 'order not found'], 404);
        }

        // Prepare the data to be updated for the OrderModel
        $updateData = [
            'orderNo' => $input->orderNo,
            'orderDate' => $input->orderDate,
            'businessNameFor' => $input->businessNameFor,
            'phoneFor' => $input->mobileNo,
            'total'=> $input->total,
            'totalItem'=> $input->totalItems,
            'totalPrice'=> $input->totalPrice,
            // You can add other fields here as necessary
        ];

        // Update the order in the OrderModel
        $updated = $OrderModel->update($orderId, $updateData);

        if (!$updated) {
            $db->transRollback();
            return $this->fail(['status' => false, 'message' => 'Failed to update order'], 500);
        }

        // Handle quotation details (multiple items)
        if (isset($input->items) && is_array($input->items)) {
            foreach ($input->items as $item) {
                // Ensure itemId is provided and valid
                if (empty($item->itemId)) {
                    $db->transRollback();
                    return $this->fail(['status' => false, 'message' => 'itemId is required and cannot be null for all items'], 400);
                }

                // Prepare the detail data for update or insert
                $detailData = [
                    'orderId' => $orderId,  // Ensure the orderId is linked to the detail
                    'itemId' => $item->itemId,  // Use the provided itemId
                    'quantity' => $item->quantity,  // Quantity
                    'rate' => $item->rate,  // Rate
                    'amount' => $item->amount,  // Amount = quantity * rate
                    // You can add more fields as needed
                ];

                // Check if orderDetailId  exists to update or if we need to insert it
                if (isset($item->orderDetailId ) && $item->orderDetailId ) {
                    // Update the existing order detail using orderDetailId 
                    $updatedDetail = $OrderDetailModel->update($item->orderDetailId , $detailData);
                    if (!$updatedDetail) {
                        $db->transRollback();
                        return $this->fail(['status' => false, 'message' => 'Failed to update order Detail for orderDetailId  ' . $item->orderDetailId ], 500);
                    }
                } else {
                    // Check if the item already exists in the order details before inserting
                    $existingItem = $OrderDetailModel->where('orderId', $orderId)
                                                        ->where('itemId', $item->itemId)
                                                        ->first();

                    if ($existingItem) {
                        // If item exists, update it instead of inserting
                        $detailData['orderDetailId'] = $existingItem['orderDetailId'];
                        $updatedDetail = $OrderDetailModel->update($existingItem['orderDetailId'], $detailData);
                        if (!$updatedDetail) {
                            $db->transRollback();
                            return $this->fail(['status' => false, 'message' => 'Failed to update existing order Detail'], 500);
                        }
                    } else {
                        // Insert a new detail if no orderDetailId  is provided and it's not already in the order
                        $insertedDetail = $OrderDetailModel->insert($detailData);
                        if (!$insertedDetail) {
                            $db->transRollback();
                            return $this->fail(['status' => false, 'message' => 'Failed to insert new order Detail'], 500);
                        }
                    }
                }
            }
        }

        // Commit the transaction if everything is successful
        $db->transCommit();

        // Return success message if both order and details are updated successfully
        return $this->respond(['status' => true, 'message' => 'order and Details Updated Successfully'], 200);
    }


    public function delete()
    {
        $input = $this->request->getJSON();
        
        // Validation rules for the customer
        $rules = [
            'orderId' => ['rules' => 'required'], // Ensure customerId is provided and is numeric
        ];

        // Validate the input
        if ($this->validate($rules)) {
           
        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
            $model = new OrderModel($db);

            // Retrieve the customer by customerId
            $orderId = $input->orderId;
            $order = $model->find($orderId); // Assuming find method retrieves the customer

            if (!$order) {
                return $this->fail(['status' => false, 'message' => 'order not found'], 404);
            }

            // Proceed to delete the customer
            $deleted = $model->delete($orderId);

            if ($deleted) {
                return $this->respond(['status' => true, 'message' => 'order Deleted Successfully'], 200);
            } else {
                return $this->fail(['status' => false, 'message' => 'Failed to delete order'], 500);
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

    public function getLastOrder()
    {
       
        $tenantService = new TenantService();
        // Connect to the tenant's database
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));
        $model = new OrderModel($db);

        // Retrieve the last order
        $lastOrder = $model->orderBy('createdDate', 'DESC')->first();

        if (!$lastOrder) {
            return $this->respond(['status' => false, 'message' => 'No orders found', 'data' => null], 200);
        }

        return $this->respond(['status' => true, 'message' => 'Last Order Fetched Successfully', 'data' => $lastOrder], 200);
    }

    
}
