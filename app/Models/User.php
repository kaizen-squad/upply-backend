<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
=======
=======
use App\Models\Client;
use App\Models\Prestataire;
>>>>>>> 45d8cb3 (fix- Review of pull request #33 taking in account)
=======
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
=======
use App\Models\Client;
use App\Models\Prestataire;
>>>>>>> 45d8cb3 (fix- Review of pull request #33 taking in account)
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
=======
use Illuminate\Database\Eloquent\Relations\HasOne;
>>>>>>> 45d8cb3 (fix- Review of pull request #33 taking in account)
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['email', 'password'])]
=======
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
=======
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['email', 'password'])]
>>>>>>> 45d8cb3 (fix- Review of pull request #33 taking in account)
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
<<<<<<< HEAD
<<<<<<< HEAD
    use HasUuids, HasFactory, Notifiable;

    protected $fillable = [
        'firstname',
        'lastname',
        'role',
        'email',
        'country',
        'password',
    ];
=======
    use HasFactory, Notifiable;
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
=======
    use HasFactory, Notifiable;
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD

    public function transactions():HasMany{
        return $this->hasMany(Transaction::class);
    }
=======
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
=======
=======
>>>>>>> 45d8cb3 (fix- Review of pull request #33 taking in account)

    public function client() : HasOne{
        return $this->hasOne(Client::class);
    }

    public function prestataire() : HasOne{
        return $this->hasOne(Prestataire::class);
    }
<<<<<<< HEAD
>>>>>>> 45d8cb3 (fix- Review of pull request #33 taking in account)
=======
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
=======
>>>>>>> 45d8cb3 (fix- Review of pull request #33 taking in account)
}
