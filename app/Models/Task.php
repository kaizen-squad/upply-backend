<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

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

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'task_id', 'id');
    }

    public function contract(): HasOneThrough
    {
        return $this->hasOneThrough(
            Contract::class,
            Application::class,
            'task_id',
            'application_id',
            'id',
            
        );
    }
}
