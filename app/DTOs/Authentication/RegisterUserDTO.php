<?php

    namespace App\DTOs\Authentication;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\Validator;

    readonly class RegisterUserDTO{

        public function __construct(
            public string $name,
            public string $email,
            public string $password,
            public ?string $role=UserRole::Prestataire,
            public ?string $phone = null,
            public float $rating_avg,
        ){}

        public static function FromValidator(Validator $array){
            $data = $array->validated();

            return new self(
                name : $data['name'],
                email : $data['email'],
                password : $data['password'],
                role : $data['role'],
                phone : $data['phone'],
                rating_avg : $data['rating_avg'],
            );
        }
    };