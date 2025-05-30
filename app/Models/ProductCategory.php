<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductCategory extends Model
{
    protected $table            = 'product_category';
    protected $primaryKey       = 'productCategoryId';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['productCategoryName',
    'description','gstTax','isActive','isDeleted', 'modifiedDate', 'modifiedBy', 'createdDate', 'createdBy'];

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
