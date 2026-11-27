<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProviderSubscribed
{
    public function handle(Request $request, Closure $next)
    {
        $provider = Auth::guard('provider')->user();

        if (!$provider || !$provider->hasActiveSubscription()) {
            $status = $provider?->effectiveSubscriptionStatus() ?? 'inactive';
            $message = match ($status) {
                'expired'   => 'انتهت مدة اشتراكك الشهري. يُرجى تجديد الاشتراك مع الإدارة لتتمكن من إنشاء فواتير جديدة.',
                'suspended' => 'تم إيقاف اشتراكك مؤقتاً من قبل الإدارة. يُرجى التواصل مع الإدارة لإعادة التفعيل.',
                default     => 'يجب تفعيل الاشتراك الشهري أولاً لتتمكن من إنشاء فواتير للعملاء. تواصل مع الإدارة لتفعيل اشتراكك فوراً.',
            };

            if ($request->expectsJson()) {
                return response()->json(['error' => $message], 403);
            }

            return redirect()->route('provider.dashboard')->with('error', $message);
        }

        return $next($request);
    }
}
