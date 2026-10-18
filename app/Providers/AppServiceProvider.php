<?php

namespace App\Providers;

use App\Auth\TabAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        Auth::viaRequest('papirus-tab', fn (Request $request) => app(TabAuthentication::class)->userForRequest($request));
    }
}
