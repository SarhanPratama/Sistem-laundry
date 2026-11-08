<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check() || !auth()->user()->hasTenantPermission($permission)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}
