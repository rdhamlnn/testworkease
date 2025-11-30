<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // Authentication middleware
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            
            // Unified middleware untuk semua role checking
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            
            // Alias untuk backward compatibility
            'admin' => \App\Http\Middleware\RoleMiddleware::class,
            'kadivmekanik' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
