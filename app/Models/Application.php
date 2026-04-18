<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'task_id',
        'prestataire_id',

        'message',
        'status'
    ];

    protected $casts = [
        'status'
    ];
}
