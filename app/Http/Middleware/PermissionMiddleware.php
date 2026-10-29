<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki izin untuk melakukan aksi ini.');
        }

        return $next($request);
    }
}
