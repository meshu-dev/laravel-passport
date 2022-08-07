<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

use App\AccessTokenRepository;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(AccessTokenRepository $accessTokenRepository)
    {
        $this->registerPolicies();
        $this->setupPassport($accessTokenRepository);
    }

    private function setupPassport(AccessTokenRepository $accessTokenRepository)
    {
        Passport::routes();

        $tokenExpiry = config('app.tokenExpiry');
        $accessTokenRepository->setTokenExpiryTimes($tokenExpiry);
    }
}
