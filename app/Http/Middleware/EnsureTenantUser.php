<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->isSuperAdmin()) {
            abort(403, 'Fitur ini tidak tersedia untuk akun Platform Superadmin.');
        }

        return $next($request);
    }
}
