<?php

<<<<<<< HEAD
<<<<<<< HEAD
namespace App\DTOs\Auth;

use Illuminate\Validation\Validator;
=======
use App\DTOs\Auth;
=======
namespace App\DTOs\Auth;

>>>>>>> 3e1eb8b (feat- Basis backend dockerization)
use Illuminate\Http\Request;
>>>>>>> 4225864 (feat- Database service & Redis service setup)

    readonly class RegisterPrestataireDTO{
        public function __construct(
            public string $firstname,
            public string $lastname,
            public string $email,
            public string $password,
<<<<<<< HEAD
            public ?string $job_title = null,
            public ?string $bio=null,
            public ?int $daily_rate=null,
            public ?array $skills=null,
        ){}

        public static function FromRequest(Validator $validate){
            $data = $validate->validated();
            return new self(
                firstname : $data['firstname'],
                lastname : $data['lastname'],
                email : $data['email'],
                password: $data['password'],
                job_title: $data['job_title'],
                bio: $data['bio'],
                daily_rate : $data['daily_rate'],
                skills : $data['skills']
=======
            public ? string $job_title = null,
            public ? string $bio=null,
            public ? int $daily_rate=null,
            public ? array $skills=null,
        ){}

        public static function FromRequest(Request $req){
            return new self(
                firstname : $req->validated('firstname'),
                lastname : $req->validated('lastname'),
                email : $req->validated('email'),
                password: $req->validated('password'),
                job_title: $req->validated('job_title'),
                bio: $req->validated('bio'),
                daily_rate : $req->validated('daily_rate'),
                skills : $req->validates('skills',[])

>>>>>>> 4225864 (feat- Database service & Redis service setup)
            );
        }
    }

?>