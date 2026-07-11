<?php

namespace Modules\Core\Event\Models;

use CodeIgniter\Model;

class SystemEventModel extends Model
{
    protected $table = 'system_events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $updatedField = '';

    protected $allowedFields = [
        'event_uuid',
        'event_name',
        'module',
        'entity_type',
        'entity_id',
        'correlation_id',
        'causation_id',
        'payload_json',
        'metadata_json',
        'event_version',
        'published_by',
        'published_at',
    ];
}
