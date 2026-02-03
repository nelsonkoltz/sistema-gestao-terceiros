<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    // app/Http/Middleware/AdminMiddleware.php
    public function handle($request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->permissao !== 'Administrador') {
            abort(403);
        }

        return $next($request);
    }

}
