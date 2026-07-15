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

    $routes->get('access/users', '\Modules\Authorization\Controllers\UserAccessController::index', ['filter' => 'permission:authorization.manage']);
    $routes->get('access/users/create', '\Modules\Authorization\Controllers\UserAccessController::create', ['filter' => 'permission:authorization.manage']);
    $routes->post('access/users', '\Modules\Authorization\Controllers\UserAccessController::store', ['filter' => 'permission:authorization.manage']);
    $routes->get('access/users/(:num)/edit', '\Modules\Authorization\Controllers\UserAccessController::edit/$1', ['filter' => 'permission:authorization.manage']);
    $routes->post('access/users/(:num)', '\Modules\Authorization\Controllers\UserAccessController::update/$1', ['filter' => 'permission:authorization.manage']);
    $routes->post('access/users/(:num)/reset-password', '\Modules\Authorization\Controllers\UserAccessController::resetPassword/$1', ['filter' => 'permission:authorization.manage']);
    $routes->get('access/users/(:num)', '\Modules\Authorization\Controllers\UserAccessController::show/$1', ['filter' => 'permission:authorization.manage']);
    $routes->post('access/users/(:num)/status', '\Modules\Authorization\Controllers\UserAccessController::updateStatus/$1', ['filter' => 'permission:authorization.manage']);
    $routes->post('access/users/(:num)/roles', '\Modules\Authorization\Controllers\UserAccessController::syncRoles/$1', ['filter' => 'permission:authorization.manage']);
    $routes->get('access/roles', '\Modules\Authorization\Controllers\RoleAccessController::index', ['filter' => 'permission:authorization.manage']);
    $routes->get('access/roles/(:num)', '\Modules\Authorization\Controllers\RoleAccessController::show/$1', ['filter' => 'permission:authorization.manage']);
    $routes->post('access/roles/(:num)/permissions', '\Modules\Authorization\Controllers\RoleAccessController::updatePermissions/$1', ['filter' => 'permission:authorization.manage']);
    $routes->post('access/roles/(:num)/status', '\Modules\Authorization\Controllers\RoleAccessController::updateStatus/$1', ['filter' => 'permission:authorization.manage']);
    $routes->get('access/teams', '\Modules\Authorization\Controllers\TeamAccessController::index', ['filter' => 'permission:teams.manage']);
    $routes->post('access/teams', '\Modules\Authorization\Controllers\TeamAccessController::store', ['filter' => 'permission:teams.manage']);
    $routes->get('access/teams/(:num)', '\Modules\Authorization\Controllers\TeamAccessController::show/$1', ['filter' => 'permission:teams.manage']);
    $routes->post('access/teams/(:num)', '\Modules\Authorization\Controllers\TeamAccessController::update/$1', ['filter' => 'permission:teams.manage']);

    $routes->get('operations', '\Modules\Operations\Controllers\OperationsController::index');
    $routes->post('operations/import-facebook-comments', '\Modules\Operations\Controllers\OperationsController::importPending');
    $routes->get('operations/(:num)', '\Modules\Operations\Controllers\OperationsController::show/$1');
    $routes->post('operations/(:num)/assign', '\Modules\Operations\Controllers\OperationsController::assign/$1', ['filter' => 'permission:operations.assign']);
    $routes->post('operations/(:num)/status', '\Modules\Operations\Controllers\OperationsController::changeStatus/$1', ['filter' => 'permission:operations.update']);
    $routes->post('operations/(:num)/priority', '\Modules\Operations\Controllers\OperationsController::changePriority/$1', ['filter' => 'permission:operations.update']);
    $routes->post('operations/(:num)/responded', '\Modules\Operations\Controllers\OperationsController::markResponded/$1', ['filter' => 'permission:operations.close']);
    $routes->post('operations/(:num)/response-draft', '\Modules\Response\Controllers\ResponseDraftController::save/$1', ['filter' => 'permission:operations.reply']);
    $routes->post('operations/(:num)/response-send', '\Modules\Response\Controllers\ResponseDraftController::send/$1', ['filter' => 'permission:operations.reply']);

    $routes->get('citizens', '\Modules\Citizens\Controllers\CitizensController::index');
    $routes->get('citizens/(:num)', '\Modules\Citizens\Controllers\CitizensController::show/$1');
    $routes->get('conversations', '\Modules\Conversations\Controllers\ConversationsController::index');
    $routes->get('conversations/(:num)', '\Modules\Conversations\Controllers\ConversationsController::show/$1');
    $routes->get('cases', '\Modules\Cases\Controllers\CasesController::index');
    $routes->get('cases/create', '\Modules\Cases\Controllers\CasesController::create', ['filter' => 'permission:cases.create']);
    $routes->post('cases/store', '\Modules\Cases\Controllers\CasesController::store', ['filter' => 'permission:cases.create']);
    $routes->get('cases/(:num)', '\Modules\Cases\Controllers\CasesController::show/$1');
    $routes->post('cases/(:num)/change-status', '\Modules\Cases\Controllers\CasesController::changeStatus/$1', ['filter' => 'permission:cases.update']);
    $routes->post('cases/(:num)/assign', '\Modules\Cases\Controllers\CasesController::assign/$1', ['filter' => 'permission:cases.assign']);
    $routes->post('cases/(:num)/unassign', '\Modules\Cases\Controllers\CasesController::unassign/$1', ['filter' => 'permission:cases.assign']);
    $routes->get('my-cases', '\Modules\Cases\Controllers\CasesController::myCases');

    $routes->get('messenger/events', '\Modules\Messenger\Controllers\MessengerEventsController::index');
    $routes->get('integration/events', '\Modules\Integration\Controllers\IntegrationReplayController::index', ['filter' => 'permission:replay.view']);
    $routes->get('integration/events/(:num)', '\Modules\Integration\Controllers\IntegrationReplayController::show/$1', ['filter' => 'permission:replay.view']);
    $routes->post('integration/events/(:num)/replay', '\Modules\Integration\Controllers\IntegrationReplayController::replay/$1', ['filter' => 'permission:replay.execute']);
    $routes->get('notifications', '\Modules\Notification\Controllers\NotificationsController::index');
    $routes->post('notifications/(:num)/read', '\Modules\Notification\Controllers\NotificationsController::markAsRead/$1');
    $routes->get('analytics', '\Modules\Analytics\Controllers\AnalyticsController::index');
    $routes->get('analytics/data', '\Modules\Analytics\Controllers\AnalyticsController::data');
    $routes->get('engagement', '\Modules\Engagement\Controllers\EngagementCenterController::index');
    $routes->get('engagement/participants', '\Modules\Engagement\Controllers\EngagementCenterController::participants');
    $routes->get('publications', '\Modules\Publication\Controllers\PublicationsController::index');
    $routes->get('publications/(:num)', '\Modules\Publication\Controllers\PublicationsController::show/$1');
    $routes->post('publications/(:num)/resolve-participants', '\Modules\Publication\Controllers\PublicationsController::resolveParticipants/$1');

    $routes->get('workflows/runtime', '\Modules\Workflow\Controllers\RuntimeInspectorController::index', ['filter' => 'permission:workflow.view']);
    $routes->get('workflows/runtime/(:num)', '\Modules\Workflow\Controllers\RuntimeInspectorController::show/$1', ['filter' => 'permission:workflow.view']);
    $routes->get('api/workflows/runtime', '\Modules\Workflow\Controllers\RuntimeInspectorController::apiIndex', ['filter' => 'permission:workflow.view']);
    $routes->get('api/workflows/runtime/(:num)', '\Modules\Workflow\Controllers\RuntimeInspectorController::apiShow/$1', ['filter' => 'permission:workflow.view']);
    $routes->get('api/workflows/runtime/(:num)/timeline', '\Modules\Workflow\Controllers\RuntimeInspectorController::timeline/$1', ['filter' => 'permission:workflow.view']);
    $routes->get('workflows/simulator', '\Modules\Workflow\Controllers\WorkflowSimulatorController::index', ['filter' => 'permission:workflow.view']);
    $routes->post('workflows/simulator/start', '\Modules\Workflow\Controllers\WorkflowSimulatorController::start', ['filter' => 'permission:workflow.manage']);
    $routes->get('workflows/simulator/(:num)', '\Modules\Workflow\Controllers\WorkflowSimulatorController::show/$1', ['filter' => 'permission:workflow.view']);
    $routes->post('workflows/simulator/(:num)/interact', '\Modules\Workflow\Controllers\WorkflowSimulatorController::interact/$1', ['filter' => 'permission:workflow.manage']);
    $routes->post('workflows/simulator/(:num)/restart', '\Modules\Workflow\Controllers\WorkflowSimulatorController::restart/$1', ['filter' => 'permission:workflow.manage']);
    $routes->get('workflows', '\Modules\Workflow\Controllers\WorkflowController::index', ['filter' => 'permission:workflow.view']);
    $routes->get('workflows/create', '\Modules\Workflow\Controllers\WorkflowController::create', ['filter' => 'permission:workflow.manage']);
    $routes->post('workflows', '\Modules\Workflow\Controllers\WorkflowController::store', ['filter' => 'permission:workflow.manage']);
    $routes->get('workflows/(:num)', '\Modules\Workflow\Controllers\WorkflowController::show/$1', ['filter' => 'permission:workflow.view']);
    $routes->post('workflows/(:num)/versions', '\Modules\Workflow\Controllers\WorkflowController::createVersion/$1', ['filter' => 'permission:workflow.manage']);
    $routes->post('workflows/(:num)/versions/(:num)/clone', '\Modules\Workflow\Controllers\WorkflowController::cloneVersion/$1/$2', ['filter' => 'permission:workflow.manage']);
    $routes->post('workflows/(:num)/versions/(:num)/publish', '\Modules\Workflow\Controllers\WorkflowController::publish/$1/$2', ['filter' => 'permission:workflow.manage']);
    $routes->post('workflows/(:num)/archive', '\Modules\Workflow\Controllers\WorkflowController::archive/$1', ['filter' => 'permission:workflow.manage']);
    $routes->get('workflows/(:num)/versions/(:num)', '\Modules\Workflow\Controllers\WorkflowVersionController::show/$1/$2', ['filter' => 'permission:workflow.view']);
    $routes->get('workflows/(:num)/versions/(:num)/nodes/create', '\Modules\Workflow\Controllers\WorkflowNodeController::create/$1/$2', ['filter' => 'permission:workflow.manage']);
    $routes->post('workflows/(:num)/versions/(:num)/nodes', '\Modules\Workflow\Controllers\WorkflowNodeController::store/$1/$2', ['filter' => 'permission:workflow.manage']);
    $routes->get('workflows/(:num)/versions/(:num)/nodes/(:num)/edit', '\Modules\Workflow\Controllers\WorkflowNodeController::edit/$1/$2/$3', ['filter' => 'permission:workflow.manage']);
    $routes->post('workflows/(:num)/versions/(:num)/nodes/(:num)', '\Modules\Workflow\Controllers\WorkflowNodeController::update/$1/$2/$3', ['filter' => 'permission:workflow.manage']);
    $routes->post('workflows/(:num)/versions/(:num)/nodes/(:num)/delete', '\Modules\Workflow\Controllers\WorkflowNodeController::delete/$1/$2/$3', ['filter' => 'permission:workflow.manage']);
    $routes->get('workflows/(:num)/versions/(:num)/transitions/create', '\Modules\Workflow\Controllers\WorkflowTransitionController::create/$1/$2', ['filter' => 'permission:workflow.manage']);
    $routes->post('workflows/(:num)/versions/(:num)/transitions', '\Modules\Workflow\Controllers\WorkflowTransitionController::store/$1/$2', ['filter' => 'permission:workflow.manage']);
    $routes->get('workflows/(:num)/versions/(:num)/transitions/(:num)/edit', '\Modules\Workflow\Controllers\WorkflowTransitionController::edit/$1/$2/$3', ['filter' => 'permission:workflow.manage']);
    $routes->post('workflows/(:num)/versions/(:num)/transitions/(:num)', '\Modules\Workflow\Controllers\WorkflowTransitionController::update/$1/$2/$3', ['filter' => 'permission:workflow.manage']);
    $routes->post('workflows/(:num)/versions/(:num)/transitions/(:num)/delete', '\Modules\Workflow\Controllers\WorkflowTransitionController::delete/$1/$2/$3', ['filter' => 'permission:workflow.manage']);
    $routes->get('workflows/(:num)/versions/(:num)/validate', '\Modules\Workflow\Controllers\WorkflowVersionController::validateVersion/$1/$2', ['filter' => 'permission:workflow.view']);
});

$routes->get('webhooks/messenger', '\Modules\Messenger\Controllers\WebhookController::verify');
$routes->post('webhooks/messenger', '\Modules\Messenger\Controllers\WebhookController::receive');
$routes->get('system/migrate', 'SystemController::migrate');
$routes->get('system/seed/(:segment)', 'SystemController::seed/$1');
