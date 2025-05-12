<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Auth extends ResourceController
{
    public function login()
    {
        $email = $this->request->getPost('userEmail');
        $password = $this->request->getPost('userPassword');

        $db = \Config\Database::connect(); // Connect to biznfc_db

        $builder = $db->table('tenant_user');
        $tenant = $builder->where('userEmail', $email)
                          ->where('isDeleted', 0)
                          ->where('isActive', 1)
                          ->get()
                          ->getRow();

        if (!$tenant) {
            return $this->respond(['status' => false, 'message' => 'User not found'], 404);
        }

        if ($password !== $tenant->userPassword) {
            return $this->respond(['status' => false, 'message' => 'Incorrect password'], 401);
        }

        // Try connecting to the tenant's DB
        try {
            $tenantDB = db_connect([
                'hostname' => $tenant->hostUrl,
                'username' => $tenant->username,
                'password' => $tenant->password,
                'database' => $tenant->databaseName,
                'DBDriver' => 'MySQLi',
            ]);

            // Example: get cards or anything from their DB
            $cards = $tenantDB->table('cards')->get()->getResult();

            return $this->respond([
                'status' => true,
                'message' => 'Login successful',
                'tenant' => [
                    'tenantName' => $tenant->tenantName,
                    'database' => $tenant->databaseName,
                    'userId' => $tenant->userId,
                    'cardUrl' => $tenant->cardUrl,
                ],
                'cards' => $cards
            ]);
        } catch (\Throwable $e) {
            return $this->respond(['status' => false, 'message' => 'DB connection failed', 'error' => $e->getMessage()], 500);
        }
    }
}