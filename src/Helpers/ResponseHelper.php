<?php
// app/Helpers/ResponseHelper.php

namespace App\Helpers;

class ResponseHelper{

    public static function success(
        string $message = "success",
        ?array $data = null,
        int $statusCode = 200
    ): \Psr\Http\Message\ResponseInterface {

        return self::create(true, $message, $data, $statusCode);

    }

    public static function error(
        string $message = "error",
        ?array $data = null,
        int $statusCode = 400
    ): \Psr\Http\Message\ResponseInterface {

        return self::create(false, $message, $data, $statusCode);

    }

    private static function create(
        bool $success,
        string $message,
        ?array $data = null,
        int $statusCode
    ): \Psr\Http\Message\ResponseInterface {

        $response = new \Slim\Psr7\Response($statusCode);
        
        $responseData = [
            "success" => $success,
            "message" => $message,
            "data" => $data
        ];
        
        $response->getBody()->write(
            /* json_encode($responseData, JSON_UNESCAPED_UNICODE) */
            json_encode($responseData)
        );
        
        return $response->withHeader('Content-Type', 'application/json');

    }
    
    // common http error responses
    public static function notFound(string $message = "Not found"){

        return self::error($message, null, 404);

    }
    
    public static function unauthorized(string $message = "Unauthorized"){

        return self::error($message, null, 401);

    }
    
    public static function created(string $message = "Created successfully", ?array $data = null){

        return self::success($message, $data, 201);

    }
}