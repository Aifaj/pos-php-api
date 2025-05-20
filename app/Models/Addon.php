<?php

namespace App\Models;

use CodeIgniter\Model;

class Addon extends Model
{
    protected $table            = 'addon_mst'; 
    protected $primaryKey       = 'addonId';  
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'addonId',
        'addonName',
        'price',
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
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'createdDate';   // Match actual field name
    protected $updatedField  = 'modifiedDate';  // Match actual field name
    protected $deletedField  = '';              // Not using soft deletes

    // Validation
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
