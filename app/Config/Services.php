<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use Modules\Core\Event\Models\SystemEventModel;
use Modules\Core\Event\Services\EventDispatcher;
use Modules\Core\Event\Services\EventEngine;
use Modules\Core\Event\Services\EventRegistry;
use Modules\Workflow\Services\RuntimeInspectorSubscriber;

class Services extends BaseService
{
    public static function eventRegistry(bool $getShared = true): EventRegistry
    {
        if ($getShared) {
            return static::getSharedInstance('eventRegistry');
        }

        $registry = new EventRegistry();
        $registry->subscribe(new RuntimeInspectorSubscriber());

        return $registry;
    }

    public static function eventDispatcher(bool $getShared = true): EventDispatcher
    {
        if ($getShared) {
            return static::getSharedInstance('eventDispatcher');
        }

        return new EventDispatcher(static::eventRegistry());
    }

    public static function eventEngine(bool $getShared = true): EventEngine
    {
        if ($getShared) {
            return static::getSharedInstance('eventEngine');
        }

        return new EventEngine(
            static::eventDispatcher(),
            new SystemEventModel(),
        );
    }
}
