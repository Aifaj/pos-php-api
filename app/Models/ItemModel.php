<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table            = 'item_mst';
    protected $primaryKey       = 'itemId';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['itemId', 'itemName','itemCode','coverImage', 'productImages', 'itemTypeId','gstAmount', 'categoryInputFieldValues', 'itemCategoryId','productCategoryId','productSubCategoryId', 'brandName', 'unit', 'unitSize', 'mrp', 'sku','startDate','duration', 'gstPercentage', 'discountType', 'discount', 'barcode', 'hsnCode', 'minStockLevel', 'description', 'tags','feature', 'termsCondition', 'type', 'portion','addons','isPortion','isAddons', 'assignBy','unitName','finalPrice','createdBy', 'createdDate', 'modifiedBy', 'modifiedDate', 'isActive', 'isDeleted'];

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

    public function getFilteredItems($categories = [], $brands = [], $minPrice = null, $maxPrice = null, $page = 1, $limit = 10)
    {
        $builder = $this->builder();

        // Filter by categories
        if (!empty($categories)) {
            $builder->whereIn('itemCategoryId', $categories);
        }

        // Filter by brands
        if (!empty($brands)) {
            $builder->whereIn('brandName', $brands);
        }

        // Filter by price range
        if ($minPrice !== null) {
            $builder->where('price >=', $minPrice);
        }

        if ($maxPrice !== null) {
            $builder->where('price <=', $maxPrice);
        }

        // Pagination: offset and limit
        $offset = ($page - 1) * $limit;
        $builder->limit($limit, $offset);

        return $builder->get()->getResult();
    }

    // This method will return the total number of filtered items (for pagination)
    public function getFilteredItemsCount($categories = [], $brands = [], $minPrice = null, $maxPrice = null)
    {
        $builder = $this->builder(); // Get the query builder
        
        // Add filters for categories, if any
        if (!empty($categories)) {
            $builder->whereIn('itemCategoryId', $categories);
        }

        // Add filters for brands, if any
        if (!empty($brands)) {
            $builder->whereIn('brandName', $brands);
        }

        // Add price range filters, if any
        if ($minPrice !== null) {
            $builder->where('price >=', $minPrice);
        }

        if ($maxPrice !== null) {
            $builder->where('price <=', $maxPrice);
        }

        // Get the total number of items that match the filters
        return $builder->countAllResults();
    }
    
}
 