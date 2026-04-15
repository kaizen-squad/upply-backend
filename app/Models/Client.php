<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

    protected $fillable = [
        "user_id",
        "company_name",
        "contact_name"
    ];

    protected $hidden = ["user_id"];

    public function user() : BelongsTo{
        return $this->belongsTo(User::class);
    }
}
