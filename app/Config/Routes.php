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

    $routes->get('operations', '\Modules\Operations\Controllers\OperationsController::index');
    $routes->post('operations/import-facebook-comments', '\Modules\Operations\Controllers\OperationsController::importPending');
    $routes->get('operations/(:num)', '\Modules\Operations\Controllers\OperationsController::show/$1');
    $routes->post('operations/(:num)/assign', '\Modules\Operations\Controllers\OperationsController::assign/$1');
    $routes->post('operations/(:num)/status', '\Modules\Operations\Controllers\OperationsController::changeStatus/$1');
    $routes->post('operations/(:num)/priority', '\Modules\Operations\Controllers\OperationsController::changePriority/$1');
    $routes->post('operations/(:num)/responded', '\Modules\Operations\Controllers\OperationsController::markResponded/$1');

    $routes->get('citizens', '\Modules\Citizens\Controllers\CitizensController::index');
    $routes->get('citizens/(:num)', '\Modules\Citizens\Controllers\CitizensController::show/$1');

    $routes->get('conversations', '\Modules\Conversations\Controllers\ConversationsController::index');
    $routes->get('conversations/(:num)', '\Modules\Conversations\Controllers\ConversationsController::show/$1');

    $routes->get('cases', '\Modules\Cases\Controllers\CasesController::index');
    $routes->get('cases/create', '\Modules\Cases\Controllers\CasesController::create');
    $routes->post('cases/store', '\Modules\Cases\Controllers\CasesController::store');
    $routes->get('cases/(:num)', '\Modules\Cases\Controllers\CasesController::show/$1');
    $routes->post('cases/(:num)/change-status', '\Modules\Cases\Controllers\CasesController::changeStatus/$1');
    $routes->post('cases/(:num)/assign', '\Modules\Cases\Controllers\CasesController::assign/$1');
    $routes->post('cases/(:num)/unassign', '\Modules\Cases\Controllers\CasesController::unassign/$1');
    $routes->get('my-cases', '\Modules\Cases\Controllers\CasesController::myCases');

    $routes->get('messenger/events', '\Modules\Messenger\Controllers\MessengerEventsController::index');
    $routes->get('notifications', '\Modules\Notification\Controllers\NotificationsController::index');
    $routes->post('notifications/(:num)/read', '\Modules\Notification\Controllers\NotificationsController::markAsRead/$1');
    $routes->get('analytics', '\Modules\Analytics\Controllers\AnalyticsController::index');
    $routes->get('analytics/data', '\Modules\Analytics\Controllers\AnalyticsController::data');
    $routes->get('engagement', '\Modules\Engagement\Controllers\EngagementCenterController::index');
    $routes->get('engagement/participants', '\Modules\Engagement\Controllers\EngagementCenterController::participants');
    $routes->get('publications', '\Modules\Publication\Controllers\PublicationsController::index');
    $routes->get('publications/(:num)', '\Modules\Publication\Controllers\PublicationsController::show/$1');
    $routes->post('publications/(:num)/resolve-participants', '\Modules\Publication\Controllers\PublicationsController::resolveParticipants/$1');

    $routes->get('workflows/runtime', '\Modules\Workflow\Controllers\RuntimeInspectorController::index');
    $routes->get('workflows/runtime/(:num)', '\Modules\Workflow\Controllers\RuntimeInspectorController::show/$1');
    $routes->get('api/workflows/runtime', '\Modules\Workflow\Controllers\RuntimeInspectorController::apiIndex');
    $routes->get('api/workflows/runtime/(:num)', '\Modules\Workflow\Controllers\RuntimeInspectorController::apiShow/$1');
    $routes->get('api/workflows/runtime/(:num)/timeline', '\Modules\Workflow\Controllers\RuntimeInspectorController::timeline/$1');

    $routes->get('workflows/simulator', '\Modules\Workflow\Controllers\WorkflowSimulatorController::index');
    $routes->post('workflows/simulator/start', '\Modules\Workflow\Controllers\WorkflowSimulatorController::start');
    $routes->get('workflows/simulator/(:num)', '\Modules\Workflow\Controllers\WorkflowSimulatorController::show/$1');
    $routes->post('workflows/simulator/(:num)/interact', '\Modules\Workflow\Controllers\WorkflowSimulatorController::interact/$1');
    $routes->post('workflows/simulator/(:num)/restart', '\Modules\Workflow\Controllers\WorkflowSimulatorController::restart/$1');

    $routes->get('workflows', '\Modules\Workflow\Controllers\WorkflowController::index');
    $routes->get('workflows/create', '\Modules\Workflow\Controllers\WorkflowController::create');
    $routes->post('workflows', '\Modules\Workflow\Controllers\WorkflowController::store');
    $routes->get('workflows/(:num)', '\Modules\Workflow\Controllers\WorkflowController::show/$1');
    $routes->post('workflows/(:num)/versions', '\Modules\Workflow\Controllers\WorkflowController::createVersion/$1');
    $routes->post('workflows/(:num)/versions/(:num)/clone', '\Modules\Workflow\Controllers\WorkflowController::cloneVersion/$1/$2');
    $routes->post('workflows/(:num)/versions/(:num)/publish', '\Modules\Workflow\Controllers\WorkflowController::publish/$1/$2');
    $routes->post('workflows/(:num)/archive', '\Modules\Workflow\Controllers\WorkflowController::archive/$1');
    $routes->get('workflows/(:num)/versions/(:num)', '\Modules\Workflow\Controllers\WorkflowVersionController::show/$1/$2');
    $routes->get('workflows/(:num)/versions/(:num)/nodes/create', '\Modules\Workflow\Controllers\WorkflowNodeController::create/$1/$2');
    $routes->post('workflows/(:num)/versions/(:num)/nodes', '\Modules\Workflow\Controllers\WorkflowNodeController::store/$1/$2');
    $routes->get('workflows/(:num)/versions/(:num)/nodes/(:num)/edit', '\Modules\Workflow\Controllers\WorkflowNodeController::edit/$1/$2/$3');
    $routes->post('workflows/(:num)/versions/(:num)/nodes/(:num)', '\Modules\Workflow\Controllers\WorkflowNodeController::update/$1/$2/$3');
    $routes->post('workflows/(:num)/versions/(:num)/nodes/(:num)/delete', '\Modules\Workflow\Controllers\WorkflowNodeController::delete/$1/$2/$3');
    $routes->get('workflows/(:num)/versions/(:num)/transitions/create', '\Modules\Workflow\Controllers\WorkflowTransitionController::create/$1/$2');
    $routes->post('workflows/(:num)/versions/(:num)/transitions', '\Modules\Workflow\Controllers\WorkflowTransitionController::store/$1/$2');
    $routes->get('workflows/(:num)/versions/(:num)/transitions/(:num)/edit', '\Modules\Workflow\Controllers\WorkflowTransitionController::edit/$1/$2/$3');
    $routes->post('workflows/(:num)/versions/(:num)/transitions/(:num)', '\Modules\Workflow\Controllers\WorkflowTransitionController::update/$1/$2/$3');
    $routes->post('workflows/(:num)/versions/(:num)/transitions/(:num)/delete', '\Modules\Workflow\Controllers\WorkflowTransitionController::delete/$1/$2/$3');
    $routes->get('workflows/(:num)/versions/(:num)/validate', '\Modules\Workflow\Controllers\WorkflowVersionController::validateVersion/$1/$2');
});

$routes->get('webhooks/messenger', '\Modules\Messenger\Controllers\WebhookController::verify');
$routes->post('webhooks/messenger', '\Modules\Messenger\Controllers\WebhookController::receive');
$routes->get('system/migrate', 'SystemController::migrate');
$routes->get('system/seed/(:segment)', 'SystemController::seed/$1');
