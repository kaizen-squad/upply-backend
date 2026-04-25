<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deliverable extends Model
{
    use HasUuids;
    
    public $timestamps = false;

    protected $fillable = [
        'prestataire_id',
        'task_id',

        'content',
        'file_path',
        'submitted_at'
    ];

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prestataire_id', 'id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
