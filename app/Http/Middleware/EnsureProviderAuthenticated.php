<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProviderAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('provider')->check()) {
            return redirect()->guest(route('login'));
        }

        $provider = Auth::guard('provider')->user();
        if (!$provider->is_active) {
            Auth::guard('provider')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'تم إيقاف حسابك مؤقتاً من قبل الإدارة. يُرجى التواصل مع الدعم.');
        }

        return $next($request);
    }
}
