<?php

declare(strict_types=1);

namespace Wikijump\Providers;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Wikijump\Actions\Fortify\CreateNewUser;
use Wikijump\Actions\Fortify\LoginResponse;
use Wikijump\Actions\Fortify\ResetUserPassword;
use Wikijump\Actions\Fortify\UpdateUserPassword;
use Wikijump\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Wikijump\Services\Authentication\Authentication;

/**
 * Bootstrapping for Laravel Fortify.
 *
 * @package Wikijump\Providers
 */
class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::authenticateUsing(function (Request $request) {
            return Authentication::handle($request);
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        // I'm not sure if this is needed, as Fortify's view routes are disabled,
        // but just in case...

        Fortify::loginView('next.auth.login');
        Fortify::registerView('next.auth.register');
        Fortify::confirmPasswordView('next.auth.confirm-password');
    }
}
