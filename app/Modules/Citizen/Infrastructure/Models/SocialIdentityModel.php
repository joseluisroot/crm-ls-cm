<?php

namespace Modules\Citizen\Infrastructure\Models;

use CodeIgniter\Model;

final class SocialIdentityModel extends Model
{
    protected $table = 'citizen_social_identities';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'uuid',
        'citizen_id',
        'channel',
        'external_id',
        'display_name',
        'actor_type',
        'confidence',
        'metadata_json',
        'is_active',
    ];
}
