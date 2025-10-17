<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\CheckAdmin; 
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use App\Http\Middleware\CheckTokenExpiration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->appendToGroup('api', \Illuminate\Http\Middleware\HandleCors::class);
        $middleware->appendToGroup('web', \Illuminate\Http\Middleware\HandleCors::class);
        
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => CheckAdmin::class,
            'check.expiration' => CheckTokenExpiration::class,
            'admin.delete' => App\Http\Middleware\AdminOnlyForDelete::class,
            'check.abonnement' => \App\Http\Middleware\CheckAbonnementExpiration::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    // // ✅ Enregistrement de ton ScheduleServiceProvider
    // ->withProviders([
    //     App\Providers\ScheduleServiceProvider::class,
    // ])
    ->create();

