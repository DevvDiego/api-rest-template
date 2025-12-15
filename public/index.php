<?php

require __DIR__ . "/../vendor/autoload.php";

use App\Auth\JWTManager;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Middleware\AuthMiddleware;

use App\Controllers\PostController;
use App\Models\Post;

Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();


$app = AppFactory::create();

$app->setBasePath('/api');

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, false, false);

// CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    
    return $response
        ->withHeader('Access-Control-Allow-Origin', $_ENV["ALLOWED_ORIGINS"])
        ->withHeader('Access-Control-Allow-Headers', $_ENV["ALLOWED_HEADERS"])
        ->withHeader('Access-Control-Allow-Methods', $_ENV["ALLOWED_METHODS"]);
});



/* 

Add pagination capabilities 

Add rate limiting via server (!IMPORTANT)

Add real verification of posts later

#### SERVER CONFIG NEEDED ####

--upload database and connect respective

*/


// TESTING PROTECTED ROUTES
$app->group('/admin', function ($group) {

    $group->get('/posts', function (Request $request, Response $response) {   
        
        $user = $request->getAttribute('user'); // Info del JWT

        $response->getBody()->write( json_encode(["success" => "dentro de admin/posts metodo GET"]) );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201); // 201 Created

    });
    
    $group->post('/posts', function (Request $request, Response $response) {

        $response->getBody()->write( json_encode(["success" => "dentro de admin/posts metodo POST"]) );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201); // 201 Created
    });

    
})->add( new AuthMiddleware( new JWTManager( $_ENV["JWT_SECRET"] ) ) ); // Middleware aplicado a TODO el grupo


$app->post('/admin/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $password = $data['password'] ?? '1234';
    
    // 1. Obtener hash del admin desde .env
    $adminHash = $_ENV["TEST_ADMIN_PASSWORD_HASH"];
    
    if (empty($adminHash)) {
        return $this->errorResponse($response, 'Configuración incompleta', 500);
    }
    
    // 2. Verificar contraseña (la más segura)
    if (!password_verify($password, $adminHash)) {
        // 6. Contraseña incorrecta
        $response->getBody()->write( json_encode(
            ['success' => false]
        ));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401); // 201 Created
    }


    // 3. Obtener instancia de JWT Manager del contenedor
    $jwtManager = new JWTManager( $_ENV["JWT_SECRET"] );

    // 4. Crear token
    $token = $jwtManager->createToken('admin');

    $response->getBody()->write( json_encode(
        [
            'success' => true,
            'token' => $token,
            'expires_in' => 24 * 3600, // 24 horas en segundos
            'user' => [
                'id' => 'admin',
                'role' => 'admin'
            ]
        ]
    ));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201); // 201 Created    

});




// Add real/useful response codes with errors, standarized

$app->get('/blog/debug/{slug}', function (Request $request, Response $response, array $args) {
    $controller = new PostController();
    $post = $controller->getPostBySlug($args['slug']);
    
    // Debug the type and value of content
    /* error_log("Content type: " . gettype($post->content));
    error_log("Content value: " . $post->content);
     */

    $data = [
        "type"=>gettype($post->content),
        "value"=>$post->content,
    ];

    // Forcefully decode it again if it's a string (as a test)
    if (is_string($post->content)) {
        $data["decodedAgainIs"] = json_decode($post->content);
    }

    $payload = json_encode(
        $data
    );
    
    
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/blog[/]', function (Request $request, Response $response){

    $controller = new PostController();

    $posts = $controller->latest(5);

    //only fetching the latest posts we recieve basic info
    //so no need to decode stored jsons of content and tags

    $response->getBody()->write(json_encode($posts));

    return $response;
});



$app->get('/blog/{slug}[/]', function (Request $request, Response $response, array $args){

    $slug = $args["slug"];
    $controller = new PostController();
    $post = $controller->getPostBySlug($slug);
    
    // Decode the JSON string into a PHP structure
    // its needed to have this as an array so the later json encode
    // takes care and encodes only once correctly for the client
    $post->content = json_decode($post->content);

    // Encode the entire object to JSON
    $payload = json_encode($post);
    $response->getBody()->write($payload);
    
    return $response->withHeader('Content-Type', 'application/json');
});



$app->post('/blog/post', function (Request $request, Response $response, array $args) {
    try {

        // parse data from the POST body
        $data = $request->getParsedBody();       
        
        if ( empty($data)) { throw new Exception("No data recieved"); }
        if( empty($data["content"]) ) { throw new Exception("Recieved data, but no content is present."); } 
        

        //take responsability of encoding in the preparation layer
        //encode to keep rich json structure
        $data["content"] = json_encode($data["content"]);
                

        //after here, the data should be ready to get in the corresponding data model


        $controller = new PostController();
        // this will throw their own exception if properties dont match
        $result = $controller->new($data);
        
        $responseData = [
            'success' => true,
            'message' => 'Success creating post',
            'data' => $result
        ];
        
        $response->getBody()->write(
            json_encode($responseData)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201); // 201 Created
            
    } catch(\Exception $e) {

        $errorData = [
            'success' => false,
            'error' => $e->getMessage()
        ];
        
        $response->getBody()->write(
            json_encode($errorData)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400); // 400 Bad Request
    }
});



$app->patch('/blog/post/{post_slug}', function (Request $request, Response $response, array $args) {
    try {

        $current_post_slug = $args["post_slug"];

        // parse data from the POST body
        $data = $request->getParsedBody();       
        
        if ( empty($data)) { throw new Exception("No data recieved"); }
        if( empty($data["content"]) ) { throw new Exception("Recieved data, but no content is present."); } 
        

        //take responsability of encoding in the preparation layer
        //encode to keep rich json structure
        $data["content"] = json_encode($data["content"]);
                

        //after here, the data should be ready to get in the corresponding data model


        $controller = new PostController();
        // this will throw their own exception if properties dont match
        $result = $controller->update($current_post_slug, $data);
        
        $responseData = [
            'success' => true,
            'message' => 'Success updating post',
            'data' => $result
        ];
        
        $response->getBody()->write(
            json_encode($responseData)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201); // 201 Created
            
    } catch(\Exception $e) {

        $errorData = [
            'success' => false,
            'error' => $e->getMessage()
        ];
        
        $response->getBody()->write(
            json_encode($errorData)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400); // 400 Bad Request
    }
});



$app->post('/login', function (Request $request, Response $response, array $args){    
    
    try {

        $logged = false;

        $data = $request->getParsedBody();

        if ( empty($data)) { throw new Exception("No data recieved"); }
        if ( empty( $data["password"] ) ) { // add quick validation for any other important field
            throw new Exception("One of the fields is missing data.");
        }

        
        $stored_admin_hash = $_ENV["TEST_ADMIN_PASSWORD_HASH"];
        $recieved_admin_password = $data["password"];


        if( password_verify($recieved_admin_password, $stored_admin_hash) ){
            $logged = true;
        }


        $response->getBody()->write(
            json_encode(
                [
                    'success' => $logged
                ]
            )
        );

        
    } catch (\Exception $e) {

        $errorData = [
            'success' => false,
            'error' => $e->getMessage()
        ];
        
        $response->getBody()->write(
            json_encode($errorData)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400); // 400 Bad Request

    }
    

    return $response;
});



$app->run();

?>
