<?php

namespace App\Helper;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTToken
{
    public static function CreateToken($userEmail,$userId):String
    {
        $key=env('JWT_KEY');
        $payload=[
            'iss' => 'laravel-token',
            'iat' => time(),
            'exp' => time() + 3600,
            'userEmail' => $userEmail,
            'userId' => $userId,
        ];
        return JWT::encode($payload, $key,'HS256');

    }
    public static function CreateTokenForSetPassword($userEmail):String
    {
        $key=env('JWT_KEY');
        $payload=[
            'iss' => 'laravel-token',
            'iat' => time(),
            'exp' => time() + 60*10,
            'userEmail' => $userEmail,
            'userId' => '0',
        ];
        return JWT::encode($payload, $key,'HS256');

    }
    public static function VerifyToken($token):object|string
    {
        try {
            if($token==null){
                return 'unauthorized';
            }
            else{
                $key =env('JWT_KEY');
                $decode=JWT::decode($token,new Key($key,'HS256'));
                return $decode;
            }
        }
        catch (Exception $e){
            return 'unauthorized';
        }
    }

}
