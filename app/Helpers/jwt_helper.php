<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getUserIdFromToken()
{
    $key = "Exiaa@11";
    $request = service('request');
    $header = $request->getHeaderLine('Authorization');
    
    $token = null;

    // extract the token from the header
    if(!empty($header)) {
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            $token = $matches[1];
        }
    }else {
        return null;
    }

    // check if token is null or empty
    if(is_null($token) || empty($token)) {
        $response = service('response');
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode([
            'status'  => false,
            'message' => 'token not found.',
        ]));
        $response->setStatusCode(401);
        return $response;
    }

    try {
        // $decoded = JWT::decode($token, $key, array("HS256"));
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return $decoded->userId ?? null;
    } catch (Exception $ex) {
        $response = service('response');
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode([
            'status'  => false,
            'message' => 'Access denied. Authentication required.',
        ]));
        $response->setStatusCode(401);
        return $response;
    }
}
