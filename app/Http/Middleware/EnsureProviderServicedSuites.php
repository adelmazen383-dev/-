<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProviderServicedSuites
{
    public function handle(Request $request, Closure $next)
    {
        $provider = Auth::guard('provider')->user();

        if (! $provider || ! $provider->isServicedSuites()) {
            abort(403, 'خاصية العقود الإلكترونية متاحة حصرياً لمقدمي خدمة الأجنحة الفندقية والشقق والعقارات المخدومة.');
        }

        return $next($request);
    }
}
