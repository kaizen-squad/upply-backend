<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'client_id',

        'title',
        'description',
        'budget',
        'deadline',
        'status'
    ];

    protected $casts = [
        'status' => TaskStatus::class
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id', 'id');
    }
}
