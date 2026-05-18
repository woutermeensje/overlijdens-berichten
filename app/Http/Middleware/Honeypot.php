<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Honeypot
{
    public function handle(Request $request, Closure $next, string $field = 'website'): Response
    {
        if ($request->isMethod('POST') && $request->filled($field)) {
            return back();
        }

        return $next($request);
    }
}
