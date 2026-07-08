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
        'last_message_at',
    ];

    protected $useTimestamps = true;
}
