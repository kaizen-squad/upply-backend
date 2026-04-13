<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'client_id',
        'prestataire_id',
        'amount',
        'currency',
        'description',
        'payment_method',
        'status'
    ];


    public function users():HasMany{
        return $this->hasMany(User::class);
    }
}
