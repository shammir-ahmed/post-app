<?php

use App\Helpers\JWT;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

if (! function_exists('public_path')) {
    /**
     * Return the path to public dir
     *
     * @param null $path
     *
     * @return string
     */
    function public_path($path = null)
    {
        return rtrim(app()->basePath('public/' . $path), '/');
    }
}

if (! function_exists('storage_path')) {
    /**
     * Return the path to public dir
     *
     * @param null $path
     *
     * @return string
     */
    function storage_path($path = null)
    {
        return rtrim(app()->basePath('storage/' . $path), '/');
    }
}


if (!function_exists('get_server_access_token')) {
    /**
     * Get admin access token to request other module
     */

     function get_server_access_token() {
         if (Cache::has('admin_access_token')) {
            return Cache::get('admin_access_token');
         }

        $user = User::find(1);

        $token = JWT::getJWT($user);

        if (Cache::put('admin_access_token', $token , intval(config('cache.ttl.jwt')))){
            return $token;
        }
     }
}


if (!function_exists('generate_username')) {

    function generate_username($user)
    {
        $username = '';
        if (!empty($user['first_name'])) {
            $username .= $user['first_name'];
        }

        if (!empty($user['last_name'])) {
            $username .= $user['last_name'];
        }

        if (empty($username)) {
            $username = explode('@', $username)[0];
        }
        $id = 0;

        if (User::whereUsername($username)->exists()) {
            $id = User::orderBy('id', 'desc')->first()->id;
        }
                
        return $id > 0? $username.($id + 1) : $username;
    }
}