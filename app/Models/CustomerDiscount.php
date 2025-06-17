<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerDiscount extends Model
{
    protected $table            = 'customer_discount'; // Corrected table name
    protected $primaryKey       = 'customerDiscountId';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'customerDiscountId', 'customerId','disType','value',
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
