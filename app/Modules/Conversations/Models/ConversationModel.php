<?php

namespace Modules\Conversations\Models;

use CodeIgniter\Model;

class ConversationModel extends Model
{
    protected $table = 'conversations';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'citizen_id',
        'channel',
        'status',
        'state',
        'last_message_at',
        'welcomed_at',
        'last_flow_payload',
    ];

    protected $useTimestamps = true;
}
