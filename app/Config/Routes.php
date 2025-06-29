<?php

use App\Controllers\ProductController;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/getproducts', 'ProductController::getProducts');
$routes->post('/product/add', 'ProductController::addProduct');
$routes->post('/product/update', 'ProductController::updateProduct');
$routes->get('/product/delete/(:num)', 'ProductController::deleteProduct/$1');

$routes->post('/user/register', 'UserController::register');
$routes->post('/user/login', 'UserController::login', ['Filter' => 'cors']);
$routes->get('/user/logout', 'UserController::logout');
$routes->post('/user/sessionvalidation', 'UserController::sessionValidation', ['Filter' => 'cors']);

$routes->get('/users/get', 'UserController::getUsers');
$routes->get('/user/get/(:any)', 'UserController::getUser/$1');

$routes->match(['post', 'options'],'/order/create', 'OrderController::createOrder', ['Filter' => 'cors']);
$routes->post('/order/insert', 'OrderController::insertOrderData');

$routes->get('/order/get', 'OrderController::getOrders');
$routes->get('/order/get/(:any)', 'OrderController::getUserOrders/$1');
$routes->get('/order/delete/(:any)', 'OrderController::deleteOrder/$1');
$routes->get('/order/process/(:any)', 'OrderController::processOrder/$1');
$routes->get('/order/done/(:any)', 'OrderController::doneOrder/$1');