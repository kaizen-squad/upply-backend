<?php

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

    readonly class RegisterPrestataireDTO{
        public function __construct(
            public string $firstname,
            public string $lastname,
            public string $email,
            public string $password,
            public ?string $job_title = null,
            public ?string $bio=null,
            public ?int $daily_rate=null,
            public ?array $skills=null,
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

            );
        }
    }

?>