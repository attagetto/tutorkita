<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless($request->user() && in_array($request->user()->role ?? 'student', $roles, true), 403, 'Kamu tidak memiliki akses untuk tindakan ini.');

        return $next($request);
    }
}
