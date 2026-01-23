<?php

namespace App\Http\Middleware;

use Closure;

class Level3Only
{
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->level !== 3) {
            auth()->logout();
            return redirect('/login')->with('error', 'Hanya user level 3 yang dapat mengakses halaman ini.');
        }
        return $next($request);
    }
}