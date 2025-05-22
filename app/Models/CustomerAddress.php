<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerAddress extends Model
{
    protected $table            = 'customer_address'; // Corrected table name
    protected $primaryKey       = 'customerAddressId';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'customerId', 'fullName', 'mobileNo', 'pincode', 'addressLine1', 'addressLine2',
        'landmark', 'city', 'state', 'country', 'deliveryInstruction',
        'isActive', 'isDeleted', 'createdDate', 'createdBy', 'modifiedDate', 'modifiedBy'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'createdDate';
    protected $updatedField  = 'modifiedDate';

    protected $beforeInsert = ['addCreatedBy'];
    protected $beforeUpdate = ['addModifiedBy'];

    protected function addCreatedBy(array $data)
    {
        helper('jwt_helper');
        $userId = getUserIdFromToken();
        if ($userId) {
            $data['data']['createdBy'] = $userId;
        }
        return $data;
    }

    protected function addModifiedBy(array $data)
    {
        helper('jwt_helper');
        $userId = getUserIdFromToken();
        if ($userId) {
            $data['data']['modifiedBy'] = $userId;
        }
        return $data;
    }
}
