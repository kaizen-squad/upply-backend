<?php 

    namespace App\Services\Authentication;

    use App\DTOs\Authentication\LoginUserDTO;
    use App\DTOs\Authentication\RegisterUserDTO;
    use App\Enums\UserRole;
    use App\Http\Resources\UserResource;
    use App\Models\User;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;
    use Illuminate\Validation\Rules\Password;
    use Laravel\Sanctum\PersonalAccessToken;

    class Authenticate{

        public static function register( Request $request )
        {
                try{
                    
                    $validator = Validator::make($request->all(),[
                        'email' => ['required', 'max:255', 'string', 'unique:users','email'],
                        'name' => ['required','max:255','string'],
                        'password' => ['required','confirmed',Password::min(8)->numbers()->letters()],
                        'role' => ['required', 'max:12', Rule::enum(UserRole::class)],
                        'phone' => ['string', 'max:100'],
                        'rating_avg' => ['decimal:2,3']
                        ]);
                        
                        if($validator->fails()){
                            return [
                                "success" => false,
                                "code" => 422,
                                "message" => $validator->errors()
                            ];
                        }
                        
                        $dto = RegisterUserDTO::FromValidator($validator);
    
                        $user = User::create([
                            'name' => $dto->name,
                            'email' => $dto->email,
                            'phone' => $dto->phone,
                            'password' => Hash::make($dto->password),
                            'role' => $dto->role,
                            'rating_avg' => $dto->rating_avg
                        ]);
                            
                            return [
                                'message' => 'user created successfully',
                                'code' => 201,
                                'success' => true, 
                                'data' => new UserResource($user)
                            ];            
                }catch(Exception $e){
                    return [
                        'success' => false,
                        'message' => $e->getMessage(),
                        'code' => 500
                    ];
                }

        }

        public static function login( Request $request){
            $validator = Validator::make($request->all(),[
                'email' => ['required','string','max:255','email'],
                'password' => ['required','string']
            ]);

            $dto = LoginUserDTO::FromValidator($validator);

            $user = User::where('email', $dto->email)->first();

            if( !$user || !Hash::check($dto->password,$user->password)){
                return [
                    'success' => false,
                    'message' => 'Invalid Credentials',
                    'code' => 401
                ];
            }else{

                $user->revokeTokens();

                $tokenAccess = $user->generateAccessToken(15,['server:access']);
                $tokenRefresh = $user->generateRefreshToken(7,['server:refresh']);

                return [
                    'success' => true,
                    'message' => 'User successfully logged in !',
                    'code' => 200,
                    'data' => [
                        'user' => $user,
                        'accessToken' => $tokenAccess,
                        'refreshToken' => $tokenRefresh
                    ]
                ];

            }

        }

        public static function refreshAccessToken(Request $request){

            $tokenString = $request->tokenString ?? $request->bearerToken();

            if(!$tokenString){
                return [
                    'success' => false,
                    'message' => 'Invalid Credentials',
                    'code' => 401
                ];
            }
            
            $token = PersonalAccessToken::findToken($tokenString);

            if( ! $token){
                return [
                    'success' => false,
                    'message' => 'Invalid Credentials',
                    'code' => 401 
                ];
            }else if( $token && $token->expires_at < now() ){

                $user = $token->tokenable;
                $user->revokeTokens();

                return [
                    'success' => false,
                    'message' => 'Invalid Credentials',
                    'code' => 401
                ];
            }else if($token && $token->cant('server:refresh')){
                return [
                    'success' => false,
                    'message' => 'Invalid Credentials',
                    'code' => 401
                ];
            }
            else{
                $user = $token->tokenable;
                $user->revokeAccessToken();

                $accessToken = $user->generateAccessToken();

                return [
                    "success" => true,
                    "message" => "User PAT created",
                    "data" => [
                        "accessToken" => $accessToken
                    ],
                    "code" => 200
                ];
            }

        }

        public static function logout(Request $request){
            $request->user()->revokeTokens();
        }

    }