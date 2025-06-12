<?php

namespace App\Models;

use CodeIgniter\Model;

class RestaurantDetails extends Model
{
    protected $table            = 'restaurant-details';  // updated table name
    protected $primaryKey       = 'resId';                // updated primary key
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'resTitle',
        'resName',
        'resQrData',
        'resAddress',
        'resContactNo',
        'resTrn',
        'resLogo',
        'isActive',
        'isDeleted',
        'createdDate',
        'createdBy',
        'modifiedDate',
        'modifiedBy'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'createdDate';
    protected $updatedField  = 'modifiedDate';
    protected $deletedField  = '';  // no soft deletes

    // Validation (empty, you can add rules later)
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
