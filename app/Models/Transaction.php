<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'task_id',
    'fedapay_transaction_id',
    'client_id',
    'prestataire_id',
    'amount_gross',
    'commission',
    'amount_net',
    'currency',
    'description',
    'payment_method',
    'status',
])]
class Transaction extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'amount_gross' => 'integer',
            'commission' => 'integer',
            'amount_net' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    public function transactionLogs(): HasMany
    {
        return $this->hasMany(TransactionLog::class, 'transaction_id', 'id');
    }
}
