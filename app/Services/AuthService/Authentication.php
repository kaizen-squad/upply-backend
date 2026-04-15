<?php

<<<<<<< HEAD
<<<<<<< HEAD
namespace App\Services\AuthService;

=======
>>>>>>> 4225864 (feat- Database service & Redis service setup)
=======
namespace App\Services\AuthService;

>>>>>>> 3e1eb8b (feat- Basis backend dockerization)
use App\DTOs\Auth\LoginDTO;
use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
<<<<<<< HEAD
<<<<<<< HEAD
use App\DTOs\Auth\RegisterPrestataireDTO;

    class Authentication{

        public static function registerUser(RegisterPrestataireDTO $DTO): JsonResponse{
=======
use RegisterPrestataireDTO;
=======
use App\DTOs\Auth\RegisterPrestataireDTO;
>>>>>>> 3e1eb8b (feat- Basis backend dockerization)

    class Authentication{

        public function registerUser(RegisterPrestataireDTO $DTO): JsonResponse{
>>>>>>> 4225864 (feat- Database service & Redis service setup)

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
<<<<<<< HEAD
<<<<<<< HEAD
                "user_id" => $user->id
=======
                "user_id" => $user
>>>>>>> 4225864 (feat- Database service & Redis service setup)
=======
                "user_id" => $user->id
>>>>>>> fdddfe8 (fix- Dockerfile key generation)
            ]);

            return response()->json([
                "success" => true,
<<<<<<< HEAD
<<<<<<< HEAD
                "message" => "Prestataire créé avec succès",
=======
                "message" => "Prestataire créer avec succès",
>>>>>>> 4225864 (feat- Database service & Redis service setup)
=======
                "message" => "Prestataire créé avec succès",
>>>>>>> 45d8cb3 (fix- Review of pull request #33 taking in account)
                "data" => $prestataire,
                "code" => 201
            ], 201);

        }

<<<<<<< HEAD
<<<<<<< HEAD
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
=======
        public function LoginUser(LoginDTO $DTO, Request $request):JsonResponse {
=======
        // public function LoginUser(LoginDTO $DTO, Request $request):JsonResponse {
>>>>>>> 45d8cb3 (fix- Review of pull request #33 taking in account)

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


<<<<<<< HEAD
        }
>>>>>>> 4225864 (feat- Database service & Redis service setup)
=======
        // }
>>>>>>> 45d8cb3 (fix- Review of pull request #33 taking in account)

    }

?>