<?php

namespace Config;
use CodeIgniter\Config\BaseService;
use Modules\Citizen\Application\CitizenResolverService;
use Modules\Citizen\Application\Queries\CitizenCardQueryService;
use Modules\Citizen\Application\Queries\CitizenTimelineQueryService;
use Modules\Citizen\Infrastructure\Publishers\CitizenIdentityEventPublisher;
use Modules\Citizen\Infrastructure\Repositories\DatabaseCitizenCardRepository;
use Modules\Citizen\Infrastructure\Repositories\DatabaseCitizenTimelineRepository;
use Modules\Citizen\Infrastructure\Repositories\DatabaseSocialIdentityRepository;
use Modules\Core\Event\Models\SystemEventModel;
use Modules\Core\Event\Services\EventDispatcher;
use Modules\Core\Event\Services\EventEngine;
use Modules\Core\Event\Services\EventRegistry;
use Modules\Operations\Application\CitizenOperationsService;
use Modules\Operations\Application\FacebookCommentWorkItemAdapter;
use Modules\Operations\Application\OperationsDetailQueryService;
use Modules\Operations\Application\OperationsQueueQueryService;
use Modules\Operations\Infrastructure\Publishers\WorkItemEventPublisher;
use Modules\Operations\Infrastructure\Repositories\DatabaseWorkItemRepository;
use Modules\Publication\Application\CommentThreadService;
use Modules\Publication\Application\PublicationAnalyticsService;
use Modules\Publication\Application\PublicationCitizenIdentityService;
use Modules\Publication\Application\PublicationProfileQueryService;
use Modules\Workflow\Repositories\WorkflowRepository;
use Modules\Workflow\Services\InstrumentedWorkflowRuntimeService;
use Modules\Workflow\Services\RuntimeInspectorQueryService;
use Modules\Workflow\Services\RuntimeInspectorSubscriber;
use Modules\Workflow\Services\WorkflowRuntimeEventPublisher;
use Modules\Workflow\Services\WorkflowRuntimeService;

class Services extends BaseService
{
    public static function eventRegistry(bool $getShared = true): EventRegistry
    {
        if ($getShared) return static::getSharedInstance('eventRegistry');
        $registry = new EventRegistry();
        $registry->subscribe(new RuntimeInspectorSubscriber());
        return $registry;
    }

    public static function eventDispatcher(bool $getShared = true): EventDispatcher
    {
        if ($getShared) return static::getSharedInstance('eventDispatcher');
        return new EventDispatcher(static::eventRegistry());
    }

    public static function eventEngine(bool $getShared = true): EventEngine
    {
        if ($getShared) return static::getSharedInstance('eventEngine');
        return new EventEngine(static::eventDispatcher(), new SystemEventModel());
    }

    public static function workflowRuntimeEventPublisher(bool $getShared = true): WorkflowRuntimeEventPublisher
    {
        if ($getShared) return static::getSharedInstance('workflowRuntimeEventPublisher');
        return new WorkflowRuntimeEventPublisher(static::eventEngine());
    }

    public static function instrumentedWorkflowRuntime(bool $getShared = true): InstrumentedWorkflowRuntimeService
    {
        if ($getShared) return static::getSharedInstance('instrumentedWorkflowRuntime');
        return new InstrumentedWorkflowRuntimeService(new WorkflowRuntimeService(), new WorkflowRepository(), static::workflowRuntimeEventPublisher());
    }

    public static function runtimeInspectorQuery(bool $getShared = true): RuntimeInspectorQueryService
    {
        if ($getShared) return static::getSharedInstance('runtimeInspectorQuery');
        return new RuntimeInspectorQueryService(db_connect());
    }

    public static function workItemRepository(bool $getShared = true): DatabaseWorkItemRepository
    {
        if ($getShared) return static::getSharedInstance('workItemRepository');
        return new DatabaseWorkItemRepository(db_connect());
    }

    public static function workItemEventPublisher(bool $getShared = true): WorkItemEventPublisher
    {
        if ($getShared) return static::getSharedInstance('workItemEventPublisher');
        return new WorkItemEventPublisher();
    }

    public static function citizenOperations(bool $getShared = true): CitizenOperationsService
    {
        if ($getShared) return static::getSharedInstance('citizenOperations');
        return new CitizenOperationsService(static::workItemRepository(), static::workItemEventPublisher());
    }

    public static function facebookCommentWorkItemAdapter(bool $getShared = true): FacebookCommentWorkItemAdapter
    {
        if ($getShared) return static::getSharedInstance('facebookCommentWorkItemAdapter');
        return new FacebookCommentWorkItemAdapter(static::citizenOperations(), static::citizenResolver(), db_connect());
    }

    public static function operationsQueueQuery(bool $getShared = true): OperationsQueueQueryService
    {
        if ($getShared) return static::getSharedInstance('operationsQueueQuery');
        return new OperationsQueueQueryService(db_connect());
    }

    public static function operationsDetailQuery(bool $getShared = true): OperationsDetailQueryService
    {
        if ($getShared) return static::getSharedInstance('operationsDetailQuery');
        return new OperationsDetailQueryService(db_connect());
    }

    public static function socialIdentityRepository(bool $getShared = true): DatabaseSocialIdentityRepository
    {
        if ($getShared) return static::getSharedInstance('socialIdentityRepository');
        return new DatabaseSocialIdentityRepository();
    }

    public static function citizenIdentityEventPublisher(bool $getShared = true): CitizenIdentityEventPublisher
    {
        if ($getShared) return static::getSharedInstance('citizenIdentityEventPublisher');
        return new CitizenIdentityEventPublisher();
    }

    public static function citizenResolver(bool $getShared = true): CitizenResolverService
    {
        if ($getShared) return static::getSharedInstance('citizenResolver');
        return new CitizenResolverService(static::socialIdentityRepository(), static::citizenIdentityEventPublisher());
    }

    public static function citizenTimelineRepository(bool $getShared = true): DatabaseCitizenTimelineRepository
    {
        if ($getShared) return static::getSharedInstance('citizenTimelineRepository');
        return new DatabaseCitizenTimelineRepository(db_connect());
    }

    public static function citizenTimeline(bool $getShared = true): CitizenTimelineQueryService
    {
        if ($getShared) return static::getSharedInstance('citizenTimeline');
        return new CitizenTimelineQueryService(static::citizenTimelineRepository());
    }

    public static function citizenCardRepository(bool $getShared = true): DatabaseCitizenCardRepository
    {
        if ($getShared) return static::getSharedInstance('citizenCardRepository');
        return new DatabaseCitizenCardRepository(db_connect());
    }

    public static function citizenCard(bool $getShared = true): CitizenCardQueryService
    {
        if ($getShared) return static::getSharedInstance('citizenCard');
        return new CitizenCardQueryService(static::citizenCardRepository());
    }

    public static function publicationProfile(bool $getShared = true): PublicationProfileQueryService
    {
        if ($getShared) return static::getSharedInstance('publicationProfile');
        return new PublicationProfileQueryService(db_connect());
    }

    public static function publicationAnalytics(bool $getShared = true): PublicationAnalyticsService
    {
        if ($getShared) return static::getSharedInstance('publicationAnalytics');
        return new PublicationAnalyticsService();
    }

    public static function commentThreads(bool $getShared = true): CommentThreadService
    {
        if ($getShared) return static::getSharedInstance('commentThreads');
        return new CommentThreadService();
    }

    public static function publicationCitizenIdentity(bool $getShared = true): PublicationCitizenIdentityService
    {
        if ($getShared) return static::getSharedInstance('publicationCitizenIdentity');
        return new PublicationCitizenIdentityService(db_connect());
    }
}
