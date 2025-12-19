<?php
namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;

use App\Helpers\ResponseHelper;

class AuthMiddleware{

    private $jwtManager;
    
    public function __construct($jwtManager){
        $this->jwtManager = $jwtManager;

    }
    
    public function __invoke(Request $request, Handler $handler): Response{
        
        //Obtener el token del header Authorization
        $authHeader = $request->getHeaderLine("Authorization");
        
        if (empty($authHeader) || !preg_match("/Bearer\s+(\S+)/", $authHeader, $matches)) {
            return $this->unauthorized("No token present for autorization");

        }
        
        $token = $matches[1];
        
        // Validar el token
        $payload = $this->jwtManager->validateToken($token);
        if (!$payload) {
            return $this->unauthorized('Invalid or expired token');

        }
        
        // Verificar que sea admin // super especifico, deberia hacerlo general???
        // En otro uso de la misma API este campo debe quitarse/comentarse
        if ( ($payload["role"] ?? "") !== "admin" ) {
            return $this->unauthorized("Insufficient privileges");
        }
        
        // Añadir informacion del usuario a la request
        $request = $request->withAttribute('user', $payload);
        
        // Continuar con la ejecución
        return $handler->handle($request);
    }
    
    private function unauthorized(string $message = "Unauthorized"): Response{

        $response = ResponseHelper::unauthorized($message);

        return $response
            ->withHeader('WWW-Authenticate', 'Bearer');
    }
}