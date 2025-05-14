<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantUserModel extends Model
{
    protected $table            = 'tenant_user';
    protected $primaryKey       = 'userId';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['userId', 'name', 'email', 'mobileNo', 'town', 'postcode', 'country', 'userType', 'countryCode','userTypeSpeid', 'username', 'password', 'uid', 'roleId', 'photoUrl', 'emailVerified', 'mobileVerified', 'aboutUs', 'location', 'themeColor', 'cardId', 'businessCategory', 'businessSubCategory', 'modifiedDate', 'modifiedBy', 'createdDate', 'createdBy', 'token', 'otp', 'otpRequestTime', 'tenantName', 'isActive', 'isDeleted'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

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
