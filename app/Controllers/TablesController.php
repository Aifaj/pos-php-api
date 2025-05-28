<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\TenantService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Database\Config;
use CodeIgniter\Database\ConnectionInterface;
use App\Models\Tables;
use App\Models\TenantModel;
use Config\Database;

class TablesController extends BaseController
{

    use ResponseTrait;

   public function addTable()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $tableModel = new Tables($db); 

    // Get JSON input
    $data = $this->request->getJSON(true);

    // Validate required fields
    if (!isset($data['tableNumber'])) {
        return $this->respond([
            'status' => false,
            'message' => 'Required fields missing: tableNumber',
        ], 400);
    }

    // Format and prepare the data
    $insertData = [
        'tableNumber'   => $data['tableNumber'],
        'waiterName'    => $data['waiterName'] ?? null,
        'tableCapacity' => $data['tableCapacity'],
        'extraInfo'     => $data['extraInfo'] ?? null,
        'isActive'      => $data['isActive'] ?? 1,
        'isDeleted'     => 0,
        'createdBy'     => $data['createdBy'] ?? null,
        'createdDate'   => date('Y-m-d H:i:s', strtotime($data['createdDate'] ?? 'now')),
    ];

    // Insert data
    if ($tableModel->insert($insertData)) {
        return $this->respond([
            'status' => true,
            'message' => 'Table added successfully',
            'data' => $insertData
        ], 200);
    } else {
        return $this->respond([
            'status' => false,
            'message' => 'Failed to add table',
            'errors' => $tableModel->errors()
        ], 500);
    }
}


public function getalltable()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $tableModel = new Tables($db);

    $tables = $tableModel->where('isDeleted', 0)->findAll();

    return $this->respond([
        'status' => true,
        'message' => 'Tables fetched successfully',
        'data' => $tables
    ], 200);
}


public function updateTable()
{
    $tenantService = new TenantService();
    $db = $tenantService->getTenantConfig($this->request->getHeaderLine('X-Tenant-Config'));

    $tableModel = new Tables($db);

    $data = $this->request->getJSON(true);

    // Ensure tableId is present
    if (!isset($data['tableId'])) {
        return $this->respond([
            'status' => false,
            'message' => 'Missing tableId in request body'
        ], 400);
    }

    $tableId = $data['tableId'];

    // Add modifiedDate and modifiedBy if needed
    $data['modifiedDate'] = date('Y-m-d H:i:s', strtotime($data['modifiedDate'] ?? 'now'));
    $data['modifiedBy'] = $data['modifiedBy'] ?? null;

    if ($tableModel->update($tableId, $data)) {
        return $this->respond([
            'status' => true,
            'message' => 'Table updated successfully',
            'data' => $data
        ], 200);
    } else {
        return $this->respond([
            'status' => false,
            'message' => 'Failed to update table',
            'errors' => $tableModel->errors()
        ], 500);
    }
}


}
