<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Prestataire extends Model
{
    /** @use HasFactory<\Database\Factories\PrestataireFactory> */
    use HasFactory;
    
    protected $fillable = [
        "firstname",
        "lastname",
        "job_title",
        "bio",
        "daily_rate",
        "skills",
        "user_id"
    ];
    protected $hidden = ["user_id"];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
}
