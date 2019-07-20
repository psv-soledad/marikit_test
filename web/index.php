<?php

use DI\Container;
use JobTest\Controller\ArticleController;
use JobTest\Controller\HomeController;
use FastRoute\RouteCollector;

/** @var Container $container */
$container = require __DIR__ . '/../app/bootstrap.php';

$dispatcher = FastRoute\simpleDispatcher(static function (RouteCollector $r) {
    $r->addRoute('GET', '/', HomeController::class);
    $r->addRoute('GET', '/article/{id}', [ArticleController::class, 'show']);
});

$route = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

switch ($route[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo '404 Not Found';
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo '405 Method Not Allowed';
        break;

    case FastRoute\Dispatcher::FOUND:
        $controller = $route[1];
        $parameters = $route[2];

        $container->call($controller, $parameters);
        break;
}
