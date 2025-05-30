<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\ItemModel;
use App\Libraries\TenantService;
use App\Models\CustomerPaymentDetails;
use App\Models\CustomerModel;
use App\Models\CustomerAddress;



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
    try {
        $input = $this->request->getJSON();

        $page       = isset($input->page) ? (int)$input->page : 1;
        $perPage    = isset($input->perPage) ? (int)$input->perPage : 10;
        $sortField  = isset($input->sortField) ? $input->sortField : 'orderId';
        $sortOrder  = isset($input->sortOrder) ? $input->sortOrder : 'asc';
        $search     = isset($input->search) ? $input->search : '';
        $filter     = isset($input->filter) ? (array)$input->filter : [];

        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

        $orderModel = new OrderModel($db);
        $orderModel->where('isDeleted', 0);

        // LIKE search
        if (!empty($search)) {
            $orderModel->groupStart()
                ->like('orderNo', $search)
                ->orLike('businessNameFor', $search)
                ->groupEnd();
        }

        // Filters
        foreach ($filter as $key => $value) {
            if (in_array($key, ['orderNo', 'orderDate', 'businessNameFor'])) {
                $orderModel->like($key, $value);
            } else if ($key === 'createdDate') {
                $orderModel->where($key, $value);
            }
        }

        // Date range filter
        if (!empty($filter['startDate']) && !empty($filter['endDate'])) {
            $orderModel->where('DATE(createdDate) >=', $filter['startDate'])
                       ->where('DATE(createdDate) <=', $filter['endDate']);
        }

        // Clone for count
        $countBuilder = clone $orderModel;
        $totalItems = $countBuilder->countAllResults(false);

        // Sorting
        if (!empty($sortField) && in_array(strtoupper($sortOrder), ['ASC', 'DESC'])) {
            $orderModel->orderBy($sortField, $sortOrder);
        }

        // Pagination
        $offset = ($page - 1) * $perPage;
        $orders = $orderModel->limit($perPage, $offset)->get()->getResultArray();

        // Get unique customerIds and customerAddressIds from orders
        $customerIds = array_unique(array_filter(array_column($orders, 'customerId')));
        $customerAddressIds = array_unique(array_filter(array_column($orders, 'customerAddressId')));

        // Fetch customers
        $customerModel = new CustomerModel($db);
        $customers = [];
        if (!empty($customerIds)) {
            $customers = $customerModel->whereIn('customerId', $customerIds)->findAll();
        }
        $customerMap = [];
        foreach ($customers as $customer) {
            $customerMap[$customer['customerId']] = $customer;
        }

        // Fetch customer addresses
        $customerAddressModel = new CustomerAddress($db);
        $addresses = [];
        if (!empty($customerAddressIds)) {
            $addresses = $customerAddressModel->whereIn('customerAddressId', $customerAddressIds)->findAll();
        }
        $addressMap = [];
        foreach ($addresses as $address) {
            $addressMap[$address['customerAddressId']] = $address;
        }

        // Attach customer and address info to orders
        foreach ($orders as &$order) {
            $custId = $order['customerId'] ?? null;
            $addrId = $order['customerAddressId'] ?? null;

            $order['customer'] = $custId && isset($customerMap[$custId]) ? $customerMap[$custId] : null;
            $order['customerAddress'] = $addrId && isset($addressMap[$addrId]) ? $addressMap[$addrId] : null;
        }

        $totalPages = ceil($totalItems / $perPage);

        return $this->respond([
            'status'    => true,
            'message'   => 'All Order Data Fetched',
            'data'      => $orders,
            'pagination'=> [
                'currentPage' => $page,
                'totalPages'  => $totalPages,
                'totalItems'  => $totalItems,
                'perPage'     => $perPage
            ]
        ], 200);
    } catch (\Throwable $e) {
        log_message('error', 'Order Paging Error: ' . $e->getMessage());

        return $this->respond([
            'status'  => false,
            'message' => 'Internal Server Error',
            'error'   => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine()
        ], 500);
    }
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
    try {
        $input = $this->request->getJSON();

        // Basic validation
        $rules = [
            'orderNo' => ['rules' => 'required'],
            'orderDate' => ['rules' => 'required'],
        ];

        if (!$this->validate($rules)) {
            return $this->fail([
                'status' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ], 409);
        }

        $tenantService = new TenantService();
        $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

        $model = new OrderModel($db);
        $paymentModel = new CustomerPaymentDetails($db); 

        // Decode JSON fields if sent as strings
        $items = is_string($input->items) ? json_decode($input->items) : $input->items;
        $taxData = isset($_POST['totalTax']) ? json_decode($_POST['totalTax'], true) : null;
        $discountData = isset($_POST['discount']) ? json_decode($_POST['discount'], true) : null;

        // Prepare data for order
        $orderData = [
            'orderNo' => $input->orderNo,
            'orderDate' => $input->orderDate,
            'orderStatus' => 'Pending',
            'customerId' => $input->customerId ?? null,
            'customerAddressId' => $input->customerAddressId ?? null,
            'finalAmount' => $input->total ?? 0,
            'shippingCost' => $input->shippingCost ?? 0,
            'totalItem' => $input->totalItem ?? count($items),
            'items' => json_encode($items),
            'totalTax' => json_encode($taxData),
            'discount' => json_encode($discountData),
        ];

        $orderId = $model->insert($orderData);

        if (!$orderId) {
            return $this->respond(['status' => false, 'message' => 'Failed to create the Order'], 500);
        }

        // Insert into customer_payment_details
        $paymentData = [
            'customerId' => $input->customerId,
            'customerName' => $input->customerName ?? 'Running Customer',
            'AddressId' => $input->customerAddressId,
            'totalAmount' => $input->transaction->totalAmount ?? 0,
            'balanceAmmount' => $input->transaction->balanceAmount ?? 0,
            'paidAmount' => $input->transaction->payingAmount ?? 0,
            'transaction'=> json_encode($input->transaction),
            'isActive' => 1,
            'isDeleted' => 0,
            'createdDate' => $input->orderDate,
            'createdBy' => $this->request->getHeaderLine('X-User-Id') ?? null,
            'modifiedDate' => $input->orderDate,
            'modifiedBy' => $this->request->getHeaderLine('X-User-Id') ?? null,
        ];

        $paymentModel->insert($paymentData);

        return $this->respond([
            'status' => true,
            'message' => 'Order and payment added successfully',
            'orderId' => $orderId
        ], 200);

    } catch (\Exception $e) {
        log_message('error', 'Order Create Error: ' . $e->getMessage());

        return $this->fail([
            'status' => false,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}

//   public function create()
// {
//     try {
//         $input = $this->request->getJSON();

//         // Validate required fields
//         $rules = [
//             'orderNo' => ['rules' => 'required'],
//             'orderDate' => ['rules' => 'required'],
//         ];

//         if (!$this->validate($rules)) {
//             return $this->fail([
//                 'status' => false,
//                 'errors' => $this->validator->getErrors(),
//                 'message' => 'Invalid Inputs'
//             ], 409);
//         }

//         $tenantService = new TenantService();
//         $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

//         $model = new OrderModel($db);
//         $itemDetailsModel = new OrderDetailModel($db);

//         // Decode items if they are a JSON string
//         $items = is_string($input->items) ? json_decode($input->items) : $input->items;

//         // Ensure shippingCost is numeric
//         $shippingCost = isset($input->shippingCost) ? floatval($input->shippingCost) : 0;

//         // Calculate total if not already
//         $total = isset($input->total) ? round(floatval($input->total), 2) : 0;

//         // Order payload
//         $orderData = [
//             'orderNo' => $input->orderNo,
//             'orderDate' => $input->orderDate,
//             'businessNameFor' => $input->businessNameFor ?? null,
//             'email' => $input->email ?? null,
//             'mobileNo' => $input->mobileNo ?? null,
//             'addressFor' => $input->addressFor ?? null,
//             'phoneFor' => $input->phoneFor ?? null,
//             'emailFor' => $input->emailFor ?? null,
//             'PanCardFor' => $input->PanCardFor ?? null,
//             'finalAmount' => $total,
//             'shippingCost' => $shippingCost,
//             'totalItem' => $input->totalItem ?? count($items),
//             'totalTax' => $input->totalTax ?? null,
//             'discount' => $input->discount ?? null,
//             'items' => $input->items
//         ];

//         $orderId = $model->insert($orderData);

//         if (!$orderId) {
//             return $this->respond(['status' => false, 'message' => 'Failed to create the Order'], 500);
//         }

//         // Insert order items
//         foreach ($items as $item) {
//             $itemData = [
//                 'orderId' => $orderId,
//                 'itemId' => $item->itemId,
//                 'quantity' => $item->quantity,
//                 'rate' => $item->finalPrice ?? 0,
//                 'amount' => $item->totalPrice ?? 0
//             ];
//             $itemDetailsModel->insert($itemData);
//         }

//         return $this->respond(['status' => true, 'message' => 'Order and items added successfully'], 200);

//     } catch (\Exception $e) {
//         log_message('error', 'Order Create Error: ' . $e->getMessage());
//         log_message('error', 'Trace: ' . $e->getTraceAsString());

//         return $this->fail([
//             'status' => false,
//             'message' => 'Internal Server Error',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }


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

   
 public function getAllCustomerTransactionByCustomerId()
{
    $input = $this->request->getJSON();
    $customerId = $input->customerId ?? null;
    $startDate = $input->startDate ?? null;
    $endDate = $input->endDate ?? null;

    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $paymentModel = new CustomerPaymentDetails($db);
    $customerModel = new CustomerModel($db);

    $builder = $paymentModel->builder();
    $builder->where('isDeleted', 0);

    if ($customerId) {
        $builder->where('customerId', $customerId);
    }

    // Add date range filtering if dates are provided
    if ($startDate && $endDate) {
    if ($startDate === $endDate) {
        // Today filter
        $builder->where("DATE(STR_TO_DATE(createdDate, '%d-%m-%Y %h:%i %p')) =", $startDate);
    } else {
        // Range filter
        $builder->where("DATE(STR_TO_DATE(createdDate, '%d-%m-%Y %h:%i %p')) >=", $startDate)
                ->where("DATE(STR_TO_DATE(createdDate, '%d-%m-%Y %h:%i %p')) <=", $endDate);
    }
}

    $payments = $builder->get()->getResult();

    // For each payment, fetch and attach customer data
    foreach ($payments as &$payment) {
        $customer = $customerModel->where('customerId', $payment->customerId)->first();
        $payment->customer = $customer;
        unset($payment->customerId); // Optional
    }

    return $this->respond([
        "status" => true,
        "message" => "All Data Fetched",
        "data" => $payments
    ], 200);
}



// public function updateOrderStatus()
// {
//     // Get input from JSON request body
//     $input = $this->request->getJSON();

//     // Validate required fields
//     $rules = [
//         'orderId' => 'required|numeric',
//         'orderStatus' => 'required|string',
//     ];

//     if (!$this->validate($rules)) {
//         return $this->fail([
//             'status' => false,
//             'errors' => $this->validator->getErrors(),
//             'message' => 'Invalid Inputs'
//         ], 409);
//     }

//     // Get tenant-specific DB configuration
//     $tenantService = new TenantService();
//     $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

//     // Instantiate the model
//     $OrderModel = new OrderModel($db);

//     // Find the order
//     $order = $OrderModel->find($input->orderId);
//     if (!$order) {
//         return $this->fail([
//             'status' => false,
//             'message' => 'Order not found'
//         ], 404);
//     }

//     // Update only the status field
//     $updated = $OrderModel->update($input->orderId, [
//         'orderStatus' => $input->orderStatus
//     ]);

//     if (!$updated) {
//         return $this->fail([
//             'status' => false,
//             'message' => 'Failed to update order status'
//         ], 500);
//     }

//     return $this->respond([
//         'status' => true,
//         'message' => 'Order status updated successfully'
//     ], 200);
// }


public function updateOrderStatus()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $orderModel = new OrderModel($db);

    // Get input data
    $data = $this->request->getJSON(true);


    $orderId = $data['orderId'];


    // Check if addon exists
    $existingOrder = $orderModel->find($orderId);
    if (!$existingOrder) {
        return $this->respond([
            'status' => false,
            'message' => 'Order not found'
        ], 404);
    }

    // Prepare fields to update
    $updateData = [];

    if (isset($data['orderId'])) {
        $updateData['orderId'] = $data['orderId'];
    }
    if (isset($data['orderNo'])) {
        $updateData['orderNo'] = $data['orderNo'];
    }

    if (isset($data['orderStatus'])) {
        $updateData['orderStatus'] = $data['orderStatus'];
    }

    
    // Perform the update
    if ($orderModel->update($orderId, $updateData)) {
        return $this->respond([
            'status' => true,
            'message' => 'Order updated successfully',
            'data' => $updateData
        ], 200);
    } else {
        return $this->respond([
            'status' => false,
            'message' => 'Failed to update address',
            'errors' => $orderModel->errors()
        ], 500);
    }
}

    
}
