<?php

require __DIR__ . "/../vendor/autoload.php";

use Slim\Factory\AppFactory;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Controllers\PostController;


Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();


$app = AppFactory::create();

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


$app->get('/blog', function (Request $request, Response $response){

    $controller = new PostController();

    $posts = $controller->latest(5);

    //only fetching the latest posts we recieve basic info
    //so no need to decode stored jsons of content and tags

    $response->getBody()->write(json_encode($posts));

    return $response;
});



$app->get('/blog/{slug}', function (Request $request, Response $response, array $args){

    $slug = $args["slug"];

    $controller = new PostController();

    $post = $controller->getPostBySlug($slug);

    
    // Decodificar jsons internos
    $post->content = json_decode($post->content, true);
    $post->tags = json_decode($post->tags, true);


    $response->getBody()->write(json_encode($post));

    return $response;
});


$app->run();

?>
