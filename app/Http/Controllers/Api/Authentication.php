<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Auth\RegisterPrestataireDTO;
use App\Http\Controllers\Controller;
use App\Services\AuthService\Authentication as AuthServiceAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class Authentication extends Controller
{
    //

    public function register(Request $request){

        
        $validate = Validator::make($request->all(),[
            "email" => ['required','string','max:255','unique:users'],
            "password" => ['required','confirmed',Password::min(8)],
            "firstname" => ['required','string','max:255','min:3'],
            "lastname" => ['required','string','max:255','min:3'],
            "job_title" => ['string','max:255','min:3'],
            "bio" => ['string','max:2500'],
            "daily_rate" => ['integer','min:1','max:4'],
            "skills" => ['array']
        ]);

        
        if($validate->fails()){
            return response()->json([
                "success" => false,
                "message"  => $validate->errors()
            ],422);
        }else{
            $DTO = RegisterPrestataireDTO::FromRequest($validate);
            return AuthServiceAuthentication::registerUser($DTO);
        }



    }


}

