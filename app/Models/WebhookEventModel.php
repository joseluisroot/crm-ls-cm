<?php

namespace App\Models;

use CodeIgniter\Model;

class WebhookEventModel extends Model
{
    protected $table = 'webhook_events';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'platform',
        'event_type',
        'sender_id',
        'recipient_id',
        'raw_payload',
        'processed',
    ];

    protected $useTimestamps = true;
}
