<?php

namespace App\Services\AuthService;

use App\DTOs\Auth\LoginDTO;
use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\DTOs\Auth\RegisterPrestataireDTO;

    class Authentication{

        public function registerUser(RegisterPrestataireDTO $DTO): JsonResponse{

            $user = User::create([
                "email" => $DTO->email,
                "password" => Hash::make($DTO->password, [
                    'memory' => 1024,
                    'time' => 2,
                    'threads' => 2
                ])
            ]);

            $prestataire = Prestataire::create([
                "firstname" => $DTO->firstname,
                "lastname" => $DTO->lastname,
                "job_title" => $DTO->job_title,
                "bio" => $DTO->bio,
                "daily_rate" => $DTO->daily_rate,
                "skills" => $DTO->skills,
                "user_id" => $user
            ]);

            return response()->json([
                "success" => true,
                "message" => "Prestataire créé avec succès",
                "data" => $prestataire,
                "code" => 201
            ], 201);

        }

        // public function LoginUser(LoginDTO $DTO, Request $request):JsonResponse {

        //     $user = User::where("email",$DTO->email);

        //     if(!$user){
        //         return response()->json([
        //             "success" => false,
        //             "message" => "Identenfiants Incorrects",
        //             "code" => 401
        //         ],401);
        //     }

        //     if( ! Hash::check($DTO->password, $user->password)){
        //         return response()->json([
        //             "success" => false,
        //             "message" => "Identenfiants Incorrects",
        //             "code" => 401
        //         ],401);
        //     }else{

        //         // $ResfreshToken = $user->createToken($request->device)
            
        //     };


        // }

    }

?>