<?php

    namespace App\DTOs\Authentication;

use Illuminate\Contracts\Validation\Validator;

    readonly class LoginUserDTO{

        public function __construct(
            public string $email,
            public string $password
        ){}

        public static function FromValidator(Validator $array){

            $data = $array->validated();

            return new self(
                email : $data['email'],
                password : $data['password']
            );

        }

    }