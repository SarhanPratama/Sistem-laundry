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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle BusinessException (and subclasses)
        $exceptions->renderable(function (\App\Exceptions\BusinessException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => [
                        'code'    => $e->getErrorCode(),
                        'message' => $e->getMessage(),
                    ],
                ], $e->getHttpStatus());
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        });

        // Handle QueryException — return user-friendly message without exposing SQL
        $exceptions->renderable(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            $message = 'Terjadi kesalahan pada database. Silakan coba lagi.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error'   => [
                        'code'    => 'DATABASE_ERROR',
                        'message' => $message,
                    ],
                ], 500);
            }
            if ($request->isMethod('get')) { return response('Database Error: ' . $e->getMessage(), 500); } return back()->withErrors(['error' => $message]);
        });
    })->create();
