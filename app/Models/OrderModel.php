<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'order_mst';
    protected $primaryKey       = 'orderId';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
protected $allowedFields = [
   'orderId',
        'orderNo',
        'orderCode',
        'orderDate',
        'items',
        'customerId',
        'customerAddressId',
        'orderStatus',
        'discount',
        'totalTax',
        'totalItem',
        'finalAmount',
        'shippingAddressId',
        'shippingCost',
        'deliveryDate',
        'orderTrackingNo',
        'orderFrom',
        'orderFromSpe',
        'orderTo',
        'orderToSpe',
        'tablejson',
        'assignBy',
        'isActive',
        'isDeleted',
        'createdBy',
        'createdDate',
        'modifiedBy',
        'modifiedDate'
];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'createdDate';
    protected $updatedField  = 'modifiedDate';
    protected $beforeInsert = ['addCreatedBy'];
    protected $beforeUpdate = ['addModifiedBy'];

    protected function addCreatedBy(array $data)
    {
        helper('jwt_helper'); // Ensure the JWT helper is loaded
        $userId = getUserIdFromToken();
        if ($userId) {
            $data['data']['createdBy'] = $userId;
        }
        return $data;
    }

    protected function addModifiedBy(array $data)
    {
        helper('jwt_helper'); // Ensure the JWT helper is loaded
        $userId = getUserIdFromToken();
        if ($userId) {
            $data['data']['modifiedBy'] = $userId;
        }
        return $data;
    }
}
