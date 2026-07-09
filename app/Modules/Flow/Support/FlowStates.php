<?php


namespace Modules\Flow\Support;

class FlowStates
{
    public const NEW = 'NEW';
    public const WAITING_CATEGORY = 'WAITING_CATEGORY';

    public const WAITING_MUNICIPALITY = 'WAITING_MUNICIPALITY';
    public const WAITING_COMMUNITY = 'WAITING_COMMUNITY';
    public const WAITING_DESCRIPTION = 'WAITING_DESCRIPTION';

    public const CASE_READY = 'CASE_READY';
    public const CASE_CREATED = 'CASE_CREATED';

    public const ATTENDING = 'ATTENDING';
    public const CLOSED = 'CLOSED';
}