<?php

namespace App\Traits;

use App\Utils\AgentUtil;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

    trait AuthToken{

        public function verifyToken(string $tokenString){
            //
            $token = PersonalAccessToken::findToken($tokenString);
            if(!$token || $this->isTokenExpired($token)){
                return false;
            }

            return true;

        }

        public function generateAccessToken(int $life=15,array $abilities=['*']){

            $name = 'access_' . AgentUtil::getDeviceName();

            $token = $this->createToken($name, $abilities);
            $token->accessToken->expires_at = Carbon::now()->addMinutes($life);
            $token->accessToken->save();
            return $token->plainTextToken;

        }

        public function generateRefreshToken( int $life=7, array $abilities = ['*']){

            $name = 'refresh_' . AgentUtil::getDeviceName();

            $token = $this->createToken($name, $abilities);
            $token->accessToken->expires_at = Carbon::now()->addDays($life);
            $token->accessToken->save();
            return $token->plainTextToken;

        }

        public function revokeTokens(){

            $accessToken = 'access_' . AgentUtil::getDeviceName();
            $refreshToken = 'refresh_' . AgentUtil::getDeviceName();

            $this->tokens()->where('name',$accessToken)->delete();
            $this->tokens()->where('name',$refreshToken)->delete();

        }

        public function revokeAccessToken(){
            $accessToken = 'access_' . AgentUtil::getDeviceName();
            $this->tokens()->where('name',$accessToken)->delete();
        }

        public function isTokenExpired(PersonalAccessToken $token){
            return $token->expires_at < now();
        }


    };