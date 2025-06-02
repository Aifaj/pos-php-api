<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Allowed origins (you can extend or modify this list as needed)
        $allowedOrigins = [
            'http://localhost:4200',
            'http://localhost:8100',
            'https://biznfc.io',
            'https://www.biznfc.io'
        ];

        // Get the origin of the request
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        // Check if the origin is in the allowed list
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            // If origin is not allowed, return an error response (optional)
            header("Access-Control-Allow-Origin: *"); // This allows all domains, not recommended for production
        }

        // Set additional CORS headers
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Tenant, x-tenant-config');
        header('Access-Control-Allow-Credentials: true'); // Allow credentials if required

        // Handle preflight OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            // Send a 204 response (no content) for preflight requests
            http_response_code(204); 
            exit();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}
