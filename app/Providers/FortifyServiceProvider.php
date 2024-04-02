<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Exceptions\BusinessException;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Sanctum\PersonalAccessToken;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for(
            'login', function (Request $request) {
                $email = (string) $request->email;

                return Limit::perMinute(5)->by($email.$request->ip());
            }
        );

        RateLimiter::for(
            'two-factor', function (Request $request) {
                return Limit::perMinute(5)->by($request->session()->get('login.id'));
            }
        );

        Fortify::authenticateUsing(
            function (Request $request) {
                if ($request->private_key) {
                    if($request->private_key === config('auth.broker_private_key')) {
                        $personal = PersonalAccessToken::findToken($request->password);
                        if ($personal) {
                            $user = User::find($personal->tokenable_id);
                            if ($user->email === $request->email) {
                                return $user;
                            }
                        }
                    }
                }
                return null;
            }
        );
    }
}
