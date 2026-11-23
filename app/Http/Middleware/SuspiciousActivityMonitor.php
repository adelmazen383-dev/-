<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

class SuspiciousActivityMonitor
{
    // Patterns that indicate malicious scanning or injection attempts
    protected array $suspiciousPatterns = [
        '/<script/i',
        '/union\s+select/i',
        '/drop\s+table/i',
        '/insert\s+into/i',
        '/exec\s*\(/i',
        '/\.\.\//i',           // path traversal
        '/etc\/passwd/i',
        '/cmd\.exe/i',
        '/powershell/i',
        '/base64_decode/i',
        '/eval\s*\(/i',
        '/\bphpinfo\b/i',
        '/\bwget\b.*http/i',
        '/\bcurl\b.*http/i',
    ];

    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $cacheKey = "suspicious_ip_{$ip}";

        // Check if IP is already blocked
        if (Cache::get("blocked_ip_{$ip}")) {
            Log::warning("BLOCKED IP attempted access", ['ip' => $ip, 'url' => $request->url()]);
            abort(403, 'تم حظر هذا الطلب.');
        }

        // Check request for suspicious content
        $rawUri = $request->getRequestUri();
        $decodedUri = rawurldecode($rawUri);
        $requestData = json_encode($request->all(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ' ' . $rawUri . ' ' . $decodedUri;
        $isSuspicious = false;

        foreach ($this->suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $requestData)) {
                $isSuspicious = true;
                break;
            }
        }

        if ($isSuspicious) {
            $attempts = Cache::increment($cacheKey, 1);
            Cache::put($cacheKey, $attempts, now()->addHours(24));

            Log::warning("SUSPICIOUS REQUEST DETECTED", [
                'ip'      => $ip,
                'url'     => $request->url(),
                'method'  => $request->method(),
                'payload' => substr($requestData, 0, 500),
                'user'    => auth()->id(),
                'attempts'=> $attempts,
            ]);

            // Block IP after 5 suspicious attempts
            if ($attempts >= 5) {
                Cache::put("blocked_ip_{$ip}", true, now()->addHours(24));
                Log::critical("IP BLOCKED after repeated suspicious activity", ['ip' => $ip]);
                abort(403, 'تم حظر هذا الطلب.');
            }

            abort(400, 'طلب غير صالح.');
        }

        return $next($request);
    }
}
