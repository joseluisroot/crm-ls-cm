<?php

namespace Modules\Conversations\Models;

use CodeIgniter\Model;

class ConversationContextModel extends Model
{
    protected $table = 'conversation_context';

    protected $primaryKey = 'id';

    protected $allowedFields = [
        'conversation_id',
        'context_key',
        'context_value',
    ];

    protected $useTimestamps = true;
}
