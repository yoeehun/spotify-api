<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Inertia::share([
            'auth' => function () {
                return [
                    'user' => Auth::user() ? Auth::user()->only('id', 'name', 'email') : null,
                ];
            },
        ]);
    }
}
