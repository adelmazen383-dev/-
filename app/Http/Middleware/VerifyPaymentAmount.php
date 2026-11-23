<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyPaymentAmount
{
    // Min and max payment amounts in SAR
    const MIN_AMOUNT = 1;
    const MAX_AMOUNT = 500000;

    public function handle(Request $request, Closure $next)
    {
        // Only apply to payment routes
        if (!$request->is('*/pay', '*/payment/*', '*/invoices*')) {
            return $next($request);
        }

        // Check amount fields
        $amountFields = ['amount', 'base_amount', 'total_payable', 'agreed_amount'];

        foreach ($amountFields as $field) {
            if ($request->has($field)) {
                $amount = (float) $request->input($field);

                if ($amount < self::MIN_AMOUNT || $amount > self::MAX_AMOUNT) {
                    Log::warning("INVALID PAYMENT AMOUNT DETECTED", [
                        'ip'     => $request->ip(),
                        'field'  => $field,
                        'amount' => $amount,
                        'url'    => $request->url(),
                        'user'   => auth()->id(),
                    ]);

                    if ($request->wantsJson()) {
                        return response()->json(['error' => 'مبلغ الدفع غير صالح.'], 422);
                    }

                    return back()->with('error', 'مبلغ الدفع غير صالح. يجب أن يكون بين ' . self::MIN_AMOUNT . ' و ' . self::MAX_AMOUNT . ' ريال.');
                }
            }
        }

        return $next($request);
    }
}
