<?php

namespace App\Models;

use CodeIgniter\Model;

class Tables extends Model
{
    protected $table            = 'table_mst';  // Updated table name
    protected $primaryKey       = 'tableId';    // Updated primary key
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'tableId',
        'tableNumber',
        'waiterName',
        'tableCapacity',
        'extraInfo',
        'tableArea',
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
    protected $createdField  = 'createdDate';
    protected $updatedField  = 'modifiedDate';
    protected $deletedField  = ''; // Not using soft deletes

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
