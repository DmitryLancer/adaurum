<?php

use Post\PostMapper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$view = new Environment($loader);

$config = include 'config/database.php';
$dsn = $config['dsn'];
$username = $config['username'];
$password = $config['password'];

try {
    $connection = new PDO($dsn, $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    echo 'Database error: ' . $exception->getMessage();
    die();
}

$postMapper = new PostMapper($connection);



$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, $args) use ($view, $postMapper) {
    $posts = $postMapper->getList('ASC');

    $body = $view->render('index.twig', [
        'posts' => $posts
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/login', function (Request $request, Response $response) use ($view) {
    $body = $view->render('section/login.twig');
    $response->getBody()->write($body);
    return $response;
});

$app->post('/login-post', function (Request $request, Response $response, $args) use ($view, $postMapper) {
    $response->getBody()->write('Login Page!');
    return $response;
});

$app->get('/register', function (Request $request, Response $response, $args) use ($view, $postMapper) {
    $body = $view->render('section/register.twig');
    $response->getBody()->write($body);
    return $response;
});

$app->post('/register-post', function (Request $request, Response $response, $args) use ($view, $postMapper) {
    $response->getBody()->write('Register Page!');
    return $response;
});

$app->get('/logout', function (Request $request, Response $response, $args) use ($view, $postMapper) {
    $response->getBody()->write('Logout Page!');
    return $response;
});

$app->get('/about', function (Request $request, Response $response, $args) use ($view) {
    $body = $view->render('about.twig', [
        'name' => 'Dmitry'
    ]);
    $response->getBody()->write($body);
    return $response;
});

$app->get('/{url_key}', function (Request $request, Response $response, $args) use ($view, $postMapper) {
    $post = $postMapper->getByUrlKey((string) $args['url_key']);

    if (empty($post)) {
        $body = $view->render('not-found.twig');
    } else {
        $body = $view->render('company.twig', [
            'post' => $post
        ]);
    }

    $response->getBody()->write($body);
    return $response;
});

$app->run();