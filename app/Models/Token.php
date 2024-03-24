<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Access\AuthorizationException;

class Token extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'device',
        'ip',
        'login_at',
        'expired',
        'session_time',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'login_at' => 'datetime',
    ];


    public static function newToken($user)
    {
        $token = self::updateOrCreate(
            [
                'user_id' => $user->id,
                'ip'    => request()->ip(),
                'device' => request()->userAgent(),
            ],
            [
                'token' => Str::random(64),
                'login_at' => Carbon::now(),
                'expired' => Carbon::now()->addDays(7)
            ]
        );

        return $token->token;
    }

    public static function varifyToken(string $t)
    {
        $token = self::where('token', $t)->first();

        if (! $token) {
            throw new AuthorizationException("Invalid Token");
        }
        
        if (Carbon::now()->isAfter($token->expired)) {
            // $token->delete();
            throw new AuthorizationException("Invalid Token");
        }

        // if ($token->ip !== request()->ip() || $token->device !== request()->userAgent()) {
        //     throw new AuthorizationException("Invalid Token 3");
        // }

        if (!Carbon::parse($token->updated_at)->isToday()) {
            // update the token daily;
            if (config('auth.admin_refresh_token') !== $t) {
                $token->token    = Str::random(64);
            }
            $token->expired  = Carbon::now()->addDays(7);
        }

        if ($token->login_at) {
            $login_at = new Carbon($token->login_at);
            $now = Carbon::now();
            $token->session_time += $login_at->diffInMinutes($now) <= 31 ? $login_at->diffInMinutes($now) : 0;
        }

        $token->login_at = Carbon::now();
        $token->save();

        return $token;
    }

    public function scopeExpired($q)
    {
        return $q->where('expired', '<', Carbon::now());
    }

    public function scopeNotExpired($q)
    {
        return $q->where('expired', '>', Carbon::now());
    }
}
