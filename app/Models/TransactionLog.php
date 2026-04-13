<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'description',
        'status',
        'metadata'
    ];

    public function transactions():BelongsTo{
        return $this->belongsTo(Transaction::class);
    }

    
}
