<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ─── Force HTTPS in production ───
        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);

        // ─── Security Headers on all responses ───
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // ─── Monitor suspicious activity ───
        $middleware->append(\App\Http\Middleware\SuspiciousActivityMonitor::class);

        // ─── Verify payment amounts ───
        $middleware->append(\App\Http\Middleware\VerifyPaymentAmount::class);

        // ─── Middleware aliases for Service Providers portal ───
        $middleware->alias([
            'provider.auth'            => \App\Http\Middleware\EnsureProviderAuthenticated::class,
            'provider.guest'           => \App\Http\Middleware\EnsureProviderGuest::class,
            'provider.subscribed'      => \App\Http\Middleware\EnsureProviderSubscribed::class,
            'provider.serviced_suites' => \App\Http\Middleware\EnsureProviderServicedSuites::class,
        ]);

        // ─── CSRF exceptions for payment webhooks ───
        $middleware->validateCsrfTokens(except: [
            '/telr/*',
            '/payment/tabby/*',
            '/payment/tamara/*',
            '/p-pay/telr/*',
            '/p-pay/tamara/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('provider/login') || $request->routeIs('provider.login*')) {
                return redirect()->route('login')
                    ->withInput($request->except('password', '_token'))
                    ->with('warning', 'انتهت صلاحية الجلسة، تم تجديدها تلقائياً. يُرجى المحاولة مجدداً.');
            }

            if ($request->is('login') || $request->routeIs('login')) {
                return redirect()->route('login')
                    ->withInput($request->except('password', '_token'))
                    ->with('warning', 'انتهت صلاحية جلسة تسجيل الدخول بسبب عدم النشاط أو تحديث الصفحة، تم تجديد الجلسة تلقائياً. يُرجى إدخال كلمة المرور والمحاولة مجدداً.');
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'انتهت صلاحية الجلسة، يُرجى تحديث الصفحة والمحاولة مجدداً.'], 419);
            }

            return redirect()->back()
                ->withInput($request->except('password', '_token'))
                ->with('warning', 'انتهت صلاحية الصفحة، تم تحديث الجلسة تلقائياً. يُرجى إعادة المحاولة.');
        });
    })->create();
