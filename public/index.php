<?php

require __DIR__ . "/../vendor/autoload.php";

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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


/* 
$app->post('/login', function (Request $request, Response $response, array $args){    
    
    
    $data = $request->getParsedBody();
    $hash = $_ENV["ADMIN_PASSWORD"];
    $logged = false;

    if( password_verify( $data["password"], $hash ) ){
        $logged = true;
    }

    $response->getBody()->write(
        json_encode(
            [
                'success' => $logged,
                'message' => 'Log in successful'
            ]
        )
    );
    

    return $response;
});

 */

$app->run();

?>
