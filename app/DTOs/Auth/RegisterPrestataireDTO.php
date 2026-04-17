<?php

namespace App\DTOs\Auth;

use Illuminate\Validation\Validator;

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
            );
        }
    }

?>