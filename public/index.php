<?php

require __DIR__ . "/../vendor/autoload.php";

use App\Auth\JWTManager;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Middleware\AuthMiddleware;

use App\Controllers\PostController;
use App\Helpers\ResponseHelper;

Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();


$app = AppFactory::create();

// $app->setBasePath('');

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

*/


$app->group('/admin', function ($group) {



    $group->post('/verify', function (Request $request, Response $response, array $args) {
        try {            
        
            // the simple fact of a request reaching this point,
            // means it passed the auth middlewate, therefore the token is valid.

            return ResponseHelper::success(
                "Valid token"
            );
                
        } catch(\Exception $e) {

            return ResponseHelper::error(
                $e->getMessage()
            );

        }
    });
   

    
    $group->post('/blog/post', function (Request $request, Response $response, array $args) {
        try {

            // parse data from the POST body
            $data = $request->getParsedBody();       
            
            if ( empty($data)) { 
                throw new Exception("No data recieved");
            }

            if( empty($data["content"]) ) { 
                throw new Exception("Recieved data, but no content is present."); 
            }
            

            //take responsability of encoding in the preparation layer
            //encode to keep rich json structure
            $data["content"] = json_encode($data["content"]);
                    

            //after here, the data should be ready to get in the corresponding data model


            $controller = new PostController();
            // this will throw their own exception if properties dont match
            $result = $controller->new($data);
            
            return ResponseHelper::success(
                "success",
                200,
                [$result]
            );
                
        } catch(\Exception $e) {

            return ResponseHelper::error(
                $e->getMessage()
            );

        }
    });



    $group->patch('/blog/post/{post_slug}', function (Request $request, Response $response, array $args) {
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
            
            return ResponseHelper::success(
                "success",
                200,
                [$result]
            );
                
        } catch(\Exception $e) {

            return ResponseHelper::error(
                $e->getMessage()
            );

        }
    });



})->add( new AuthMiddleware( new JWTManager( $_ENV["JWT_SECRET"] ) ) ); // Middleware aplicado a TODO el grupo


// take care of more than an admin user? or keep it simple and keep it as is

$app->post('/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $password = $data['password'] ?? '';
    
    $adminHash = $_ENV["ADMIN_PASSWORD_HASH"];
    
    if ( empty($adminHash) ) {
        return ResponseHelper::unauthorized();
    }
    

    if ( !password_verify($password, $adminHash) ) {
        return ResponseHelper::unauthorized();
    }


    $jwtManager = new JWTManager( $_ENV["JWT_SECRET"] );
    $token = $jwtManager->createToken('admin');

    return ResponseHelper::success(
        "Log in successfull",
        200,
        [
            "token" => $token,
            "expires_in" => 24 * 3600 // 24 hrs
        ],
    );

});





$app->get('/blog', function (Request $request, Response $response){

    $controller = new PostController();

    $posts = $controller->latest(5);

    //only fetching the latest posts we recieve basic info
    //so no need to decode stored jsons of content and tags

    return ResponseHelper::success(
        "success",
        200,
        $posts
    );

});



$app->get('/blog/{slug}', function (Request $request, Response $response, array $args){

    $slug = $args["slug"];
    $controller = new PostController();
    $post = $controller->getPostBySlug($slug);

    if($post == null){
        return ResponseHelper::notFound();
    };
    
    // Decode the JSON string into a PHP structure
    // its needed to have this as an array so the later json encode
    // takes care and encodes only once correctly for the client
    $post->content = json_decode($post->content);
    
    return ResponseHelper::success(
        "success",
        200,
        [$post]
    );

});



$app->run();

?>
