<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'PublicPageController::home');
$routes->get('privacy-policy', 'PublicPageController::privacyPolicy');

$routes->get('admin/login', '\Modules\Auth\Controllers\AuthController::login');
$routes->post('admin/login', '\Modules\Auth\Controllers\AuthController::attemptLogin');
$routes->get('admin/logout', '\Modules\Auth\Controllers\AuthController::logout');

$routes->group('admin', ['filter' => 'adminAuth'], static function ($routes) {
    $routes->get('/', '\Modules\Dashboard\Controllers\DashboardController::index');

    $routes->get('citizens', '\Modules\Citizens\Controllers\CitizensController::index');
    $routes->get('citizens/(:num)', '\Modules\Citizens\Controllers\CitizensController::show/$1');

    $routes->get('conversations', '\Modules\Conversations\Controllers\ConversationsController::index');
    $routes->get('conversations/(:num)', '\Modules\Conversations\Controllers\ConversationsController::show/$1');

    $routes->get('cases', '\Modules\Cases\Controllers\CasesController::index');
    $routes->get('cases/create', '\Modules\Cases\Controllers\CasesController::create');
    $routes->post('cases/store', '\Modules\Cases\Controllers\CasesController::store');
    $routes->get('cases/(:num)', '\Modules\Cases\Controllers\CasesController::show/$1');

    $routes->get('messenger/events', '\Modules\Messenger\Controllers\MessengerEventsController::index');

    $routes->post('cases/(:num)/change-status', '\Modules\Cases\Controllers\CasesController::changeStatus/$1');
    $routes->post('cases/(:num)/assign', '\Modules\Cases\Controllers\CasesController::assign/$1');
});

$routes->get('webhooks/messenger', '\Modules\Messenger\Controllers\WebhookController::verify');
$routes->post('webhooks/messenger', '\Modules\Messenger\Controllers\WebhookController::receive');

$routes->get('system/migrate', 'SystemController::migrate');
$routes->get('system/seed/(:segment)', 'SystemController::seed/$1');




