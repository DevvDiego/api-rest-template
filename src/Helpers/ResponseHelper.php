<?php
// app/Helpers/ResponseHelper.php

namespace App\Helpers;

class ResponseHelper{

    public static function success(
        string $message = "success",
        int $statusCode = 200,
        ?array $data = null
    ): \Psr\Http\Message\ResponseInterface {

        return self::create(true, $message, $statusCode, $data);

    }

    public static function error(
        string $message = "error",
        int $statusCode = 400,
        ?array $data = null
    ): \Psr\Http\Message\ResponseInterface {

        return self::create(false, $message, $statusCode, $data);

    }

    private static function create(
        bool $success,
        string $message,
        int $statusCode,
        ?array $data = null
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

        return self::error($message, 404, null);

    }
    
    public static function unauthorized(string $message = "Unauthorized"){

        return self::error($message, 401, null);

    }
    
    public static function created(string $message = "Created successfully", ?array $data = null){

        return self::success($message, 201, $data);

    }
}