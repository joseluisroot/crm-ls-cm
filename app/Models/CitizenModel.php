<?php

namespace App\Models;

use CodeIgniter\Model;

class CitizenModel extends Model
{
    protected $table = 'citizens';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'facebook_id',
        'name',
        'phone',
        'email',
        'municipality',
        'community',
        'sentiment_score',
        'status',
    ];

    protected $useTimestamps = true;
}
