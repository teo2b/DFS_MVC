<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Core\Router;
use App\Core\View;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

session_start();

$router = new Router();
$router->get('/', [new HomeController(), 'index']);
$router->get('/login', [new AuthController(), 'showLogin']);
$router->post('/login', [new AuthController(), 'login']);
$router->get('/register', [new AuthController(), 'showRegister']);
$router->post('/register', [new AuthController(), 'register']);
$router->post('/logout', [new AuthController(), 'logout']);
$router->get('/products', [new ProductController(), 'index']);
$router->get('/products/create', [new ProductController(), 'create']);
$router->post('/products', [new ProductController(), 'store']);
$router->get('/products/{id}/edit', [new ProductController(), 'edit']);
$router->post('/products/{id}/update', [new ProductController(), 'update']);
$router->post('/products/{id}/delete', [new ProductController(), 'delete']);
$router->get('/cart', [new CartController(), 'index']);
$router->post('/cart/add/{id}', [new CartController(), 'add']);
$router->post('/cart/{id}/update', [new CartController(), 'update']);
$router->post('/cart/{id}/remove', [new CartController(), 'remove']);
$router->post('/cart/clear', [new CartController(), 'clear']);

$handled = $router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);

if (!$handled) {
    http_response_code(404);
    View::render('errors/404', ['title' => 'Page introuvable']);
}
