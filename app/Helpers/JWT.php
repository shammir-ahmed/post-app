<?php

namespace App\Helpers;

use Firebase\JWT\Key;
use \Firebase\JWT\JWT as FJWT;

class JWT
{
    protected static $algorithms = ['HS256', 'HS384', 'HS512'];

    protected static $secret = "iLCJhdWQiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAiLCJpYXQiOjE2OTA1NDUxNzMsIm5iZiI6MTY5MDU0NTE3MywiZXhwIj";

    public static function getJWT($user)
    {
        $payload = array(
            "iss"  => "http://localhost:8000",
            "aud"  => "http://localhost:8000",
            "iat"  => time(),
            "nbf"  => time(),
            "exp"  => time() + intval(config('cache.ttl.jwt')),                         // 30 min
            'user' => [
                "id"          => $user->id,
                "full_name"   => $user->full_name,
                "email"       => $user->email,
                "phone"       => $user->phone,
                "online"      => $user->online,
                "avatar"      => $user->avatar,
                "status"      => $user->status,
            ]
        );

        // $secret = rand(0, sizeof(config('auth.secret_token')) - 1);
        // shuffle(self::$algorithms);


        return FJWT::encode($payload, self::$secret, 'HS256');
    }

    public static function getPayload($token)
    {
        // $secret = rand(0, sizeof(config('auth.secret_token')) - 1);
        try {
            return FJWT::decode($token, new Key(self::$secret, 'HS256'));
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new \Illuminate\Auth\AuthenticationException($e->getMessage());
        } catch (\UnexpectedValueException $e) {
            throw new \Illuminate\Auth\AuthenticationException('Unauthenticated');
        }
    }
}
