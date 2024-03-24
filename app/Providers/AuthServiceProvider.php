<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Helpers\JWT;
use App\Models\User;
use App\Helpers\GenericUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            $token = $this->getBearerToken($request);

            if (Cache::get("users.$token")) {
                throw new \Illuminate\Auth\AuthenticationException('Unauthenticated');
            }

            if ($token && ($payload = JWT::getPayload($token))) {
                return User::find($payload->user->id);
            }
        });

        Gate::define('support ticket read', function (GenericUser $user) {
            return $user->isAdministrator() || $user->hasPermission('support ticket read');
        });

        Gate::define('support ticket create', function (GenericUser $user) {
            return $user->isAdministrator() || $user->hasPermission('support ticket create');
        });

        Gate::define('support ticket update', function (GenericUser $user) {
            return $user->isAdministrator() || $user->hasPermission('support ticket update');
        });

        Gate::define('support ticket reply', function (GenericUser $user) {
            return $user->isAdministrator() || $user->hasPermission('support ticket reply');
        });

        Gate::define('support ticket delete', function (GenericUser $user) {
            return $user->isAdministrator() || $user->hasPermission('support ticket delete');
        });

        Gate::define('support ticket assign', function (GenericUser $user) {
            return $user->isAdministrator() || $user->hasPermission('support ticket assign');
        });

        Gate::define('support ticket open', function (GenericUser $user) {
            return $user->isAdministrator() || $user->hasPermission('support ticket open');
        });

        Gate::define('support ticket cancel', function (GenericUser $user) {
            return $user->isAdministrator() || $user->hasPermission('support ticket cancel');
        });

        Gate::define('support ticket close', function (GenericUser $user) {
            return $user->isAdministrator() || $user->hasPermission('support ticket close');
        });
    }

    private function getBearerToken($request)
    {
        $headers = $request->header('authorization');
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
