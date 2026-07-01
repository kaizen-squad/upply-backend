<?php

    namespace App\DTOs\Authentication;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\Validator;

    readonly class RegisterUserDTO{

        public function __construct(
            public string $name,
            public string $email,
            public string $password,
            public ?string $role= UserRole::Prestataire->value,
            public ?string $phone = null,
        ){}

        public static function FromValidator(Validator $validator){
            $data = $validator->validated();

            return new self(
                name : $data['name'],
                email : $data['email'],
                password : $data['password'],
                role : $data['role'] ?? UserRole::Prestataire->value,
                phone : $data['phone'] ?? null,
            );
        }
    };