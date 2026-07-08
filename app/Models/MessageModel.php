<?php

namespace App\Models;

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
    ];

    protected $useTimestamps = true;
}
