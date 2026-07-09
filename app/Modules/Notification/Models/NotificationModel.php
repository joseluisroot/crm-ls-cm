<?php

namespace Modules\Notification\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'channel',
        'recipient_type',
        'recipient_id',
        'subject',
        'body',
        'status',
        'payload',
        'sent_at',
        'read_at',
    ];

    protected $useTimestamps = true;
}