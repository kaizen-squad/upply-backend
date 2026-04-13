<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'client_id',
        'prestataire_id',
        'amount',
        'currency',
        'description',
        'payment_method',
        'status'
    ];


    public function users():BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function transactionlogs():HasMany{
        return $this->hasMany(TransactionLog::class);
    }
}
