<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('user', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->post('sessionvalidation', 'UserController::sessionValidation');
    $routes->post('register', 'UserController::register');
    $routes->post('login', 'UserController::login');
    $routes->get('logout', 'UserController::logout');
    $routes->get('get', 'UserController::getUsers');
    $routes->get('get/(:any)', 'UserController::getUser/$1');
});

$routes->group('product', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('get', 'ProductController::getProducts');
    $routes->get('delete/(:num)', 'ProductController::deleteProduct/$1');
    $routes->post('add', 'ProductController::addProduct');
    $routes->post('update', 'ProductController::updateProduct');
});

$routes->group('order', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->post('create', 'OrderController::createOrder');
    $routes->get('get', 'OrderController::getOrders');
    $routes->get('get/(:any)', 'OrderController::getUserOrders/$1');
    $routes->get('delete/(:any)', 'OrderController::deleteOrder/$1');
    $routes->get('process/(:any)', 'OrderController::processOrder/$1');
    $routes->get('done/(:any)', 'OrderController::doneOrder/$1');
});


