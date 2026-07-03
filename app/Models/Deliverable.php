<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: $this->file_path
                ? fn () => Storage::disk('public')->url($this->file_path)
                : null
        );
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prestataire_id', 'id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
