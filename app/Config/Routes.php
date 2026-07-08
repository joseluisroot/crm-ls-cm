<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'PublicPageController::home');
$routes->get('privacy-policy', 'PublicPageController::privacyPolicy');

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'DashboardController::index');

    $routes->get('citizens', 'CitizensController::index');
    $routes->get('citizens/(:num)', 'CitizensController::show/$1');

    $routes->get('conversations', 'ConversationsController::index');
    $routes->get('conversations/(:num)', 'ConversationsController::show/$1');

    $routes->get('cases', 'CasesController::index');
    $routes->get('cases/create', 'CasesController::create');
    $routes->post('cases/store', 'CasesController::store');
    $routes->get('cases/(:num)', 'CasesController::show/$1');
});

$routes->get('webhooks/messenger', 'Webhooks\MessengerController::verify');
$routes->post('webhooks/messenger', 'Webhooks\MessengerController::receive');


