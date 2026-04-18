<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'task_id',
        'prestataire_id',
        'contract_id',

        'message',
        'status'
    ];

    protected $casts = [
        'status' => ApplicationStatus::class
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prestataire_id', 'id');
    }
}
