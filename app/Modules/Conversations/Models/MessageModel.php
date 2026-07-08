<?php

namespace Modules\Conversations\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'conversation_id',
        'direction',
        'message_type',
        'body',
        'raw_payload',
        'sentiment',
        'category',
        'priority',
        'sent_status',
        'sent_at',
        'delivery_error',
    ];

    protected $useTimestamps = true;
}
