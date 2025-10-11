<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\RegisterResponse; // ★追加：カスタムレスポンスを読み込む
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract; // ★追加：契約をインポート

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

        Fortify::registerView(function () {
            return view('auth.register.step1');
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        // ★追加：新規登録成功時のレスポンスを、カスタムクラスに差し替える
        $this->app->singleton(
            RegisterResponseContract::class,
            RegisterResponse::class
        );

        RateLimiter::for('auth.login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // 通常のログイン成功時のリダイレクトは、RouteServiceProvider::HOMEに委ねられる

    }
}
