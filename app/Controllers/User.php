<?php
 
namespace App\Controllers;
 
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;

class User extends BaseController
{
    use ResponseTrait;
     
    public function index()
    {
        $users = new UserModel;
        return $this->respond(["status" => true, "message" => "All Data Fetched", "data" => $users->findAll()], 200);
    }



   public function login_post()
{
    $data = $this->request->getJSON(true);
    $email = $data['userEmail'] ?? null;
    $password = $data['userPassword'] ?? null;

    if (!$email || !$password) {
        return $this->respond([
            'status' => false,
            'message' => 'Email or password missing'
        ], 400);
    }

    $userModel = new \App\Models\UserModel();
    $user = $userModel->where('userEmail', $email)
                     ->where('isDeleted', 0)
                     ->where('isActive', 1)
                     ->first();

    if ($user && $password === $user['userPassword']) {
       // $dbName = $user['databaseName'];

        //$tenantDb = \App\Libraries\TenantDatabase::connectTo($dbName);

        $_SESSION['posUser'] = $user;

        return $this->respond([
            'status' => true,
            'message' => 'Login successful',
            'tenant' => $user,
        ], 200);
    }

    return $this->respond([
        'status' => false,
        'message' => 'Invalid credentials'
    ], 401);
}


    public function test_post()
    {
      $orderNo = 'mcq2hTPYwEtTLk0IjQCP';
      $userModel = new \App\Models\OrderModel();

    $user = $userModel->where('orderNo', $orderNo)
                     ->where('isDeleted', 0)
                     ->where('isActive', 1)
                     ->first();

    if ($user) {
        return $this->respond([
            'status' => true,
            'message' => 'Test POST function is working!',
            'data' => $user
        ], 200);
    }

       
    }


     public function payment_post()
    {

        $data = $this->request->getJSON(true);
        $posUser = $data['posUser'] ?? null;

        $paymentId = '1';

        if($posUser) {
           
            try {

                $tenantDb = \App\Libraries\TenantDatabase::connectTo($posUser); // Ensure you have a method like this

                $paymentModel = new \App\Models\PaymentDetailModel($tenantDb); // Pass tenantDb to the model

                $paymentDetails = $paymentModel->where('paymentId', $paymentId)
                                            ->where('isDeleted', 0)
                                            ->where('isActive', 1)
                                            ->first();

                if ($paymentDetails) {
                    return $this->respond([
                        'status' => true,
                        'message' => 'Payment details fetched successfully!',
                        'data' => $paymentDetails
                    ], 200);
                } else {
                    return $this->respond([
                        'status' => false,
                        'message' => 'Payment details not found',
                    ], 404);
                }
            } catch (\Throwable $e) {

                return $this->respond([
                    'status' => false,
                    'message' => 'Failed to connect to tenant database',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return $this->respond([
                'status' => false,
                'message' => 'User not found or invalid paymentId',
            ], 404);
        }
    }


}
