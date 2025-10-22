<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Http\Middleware\ApiKeyAuth;
use App\Providers\RouteServiceProvider; // اگر داریش

return Application::configure(basePath: dirname(__DIR__))
    // 📌 اگر RouteServiceProvider داری، اینجا ثبتش کن
    ->withProviders([
        RouteServiceProvider::class,
    ])

    // 📍 فایل‌های مربوط به routing
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    // 🧱 تعریف middlewareها
    ->withMiddleware(function (Middleware $middleware): void {

        // middleware سراسری (global)
        $middleware->append(ApiKeyAuth::class);

        // alias برای استفاده در route
        $middleware->alias([
            'check.age' => ApiKeyAuth::class,
            'throttle'  => ThrottleRequests::class,
        ]);

        // گروه middleware برای api
        $middleware->group('api', [
            ThrottleRequests::class . ':api', // rate limiter با نام api
        ]);
    })

    // ⚠️ هندل خطاها
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
