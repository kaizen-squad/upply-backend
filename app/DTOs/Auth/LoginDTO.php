<?php

    namespace App\DTOs\Auth ;

    use Illuminate\Http\Request;

    readonly class LoginDTO{

        public function __construct(
            public string $email,
            public string $password
        ){}

        public static function FromRequest(Request $request){
            return new self(
                email : $request->validated('email'),
                password : $request->validated('password')
            );
        }


    }


?> 