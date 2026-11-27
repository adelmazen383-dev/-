<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProviderGuest
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('provider')->check()) {
            return redirect()->route('provider.dashboard');
        }

        return $next($request);
    }
}
