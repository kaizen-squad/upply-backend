<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PersonalAccessToken extends SanctumToken
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
}
