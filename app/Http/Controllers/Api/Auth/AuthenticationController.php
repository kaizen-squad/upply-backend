<?php

    namespace App\Http\Controllers\Api\Auth;

    use App\Services\Authentication\Authenticate;
    use Exception;
    use Illuminate\Http\Request;

    class AuthenticationController{

        public function register(Request $request){

            try{
    
                $Data = Authenticate::register($request);

                return response()->json($Data, $Data['code'] ?? 201);
                
            }catch(Exception $e){
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 500
                ],500);
            }
        }

        public function login(Request $request){
            try{

                $data = Authenticate::login($request);

                return response()->json($data, $data['code'] ?? 200);

            }catch(Exception $e){
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 500
                ],500);
            }
        }

    }
