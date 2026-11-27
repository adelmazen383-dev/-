<?php

namespace App\Services;

use App\Mail\ProviderPasswordResetOtpMail;
use App\Models\ProviderPasswordOtp;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProviderOtpService
{
    public const OTP_TTL_MINUTES = 10;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;

    /**
     * Find a service provider by email OR normalized phone number.
     */
    public function findProviderByIdentifier(string $identifier): ?ServiceProvider
    {
        $clean = trim($identifier);
        if ($clean === '') {
            return null;
        }

        if (str_contains($clean, '@')) {
            return ServiceProvider::whereRaw('LOWER(email) = ?', [strtolower($clean)])->first();
        }

        $normalizedPhone = ServiceProvider::normalizePhone($clean);

        return ServiceProvider::where('phone', $normalizedPhone)
            ->orWhere('phone', $clean)
            ->first();
    }

    /**
     * Generate a secure 6-digit OTP, store its hash, and email it to the provider.
     */
    public function sendResetOtp(ServiceProvider $provider, ?string $ip = null): array
    {
        // 1. Enforce resend cooldown
        $recent = ProviderPasswordOtp::where('service_provider_id', $provider->id)
            ->where('created_at', '>', now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))
            ->latest('id')
            ->first();

        if ($recent) {
            $elapsed   = (int) $recent->created_at->diffInSeconds(now());
            $remaining = max(1, self::RESEND_COOLDOWN_SECONDS - $elapsed);

            return [
                'success'  => false,
                'cooldown' => $remaining,
                'error'    => "تم إرسال رمز تحقق للتو. يُرجى الانتظار {$remaining} ثانية قبل طلب رمز جديد.",
            ];
        }

        // 2. Generate cryptographically secure 6-digit OTP
        $plainCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 3. Invalidate previous unused OTPs & persist new hashed OTP
        $otpRecord = DB::transaction(function () use ($provider, $plainCode, $ip) {
            ProviderPasswordOtp::where('service_provider_id', $provider->id)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            return ProviderPasswordOtp::create([
                'service_provider_id' => $provider->id,
                'email'               => $provider->email,
                'phone'               => $provider->phone,
                'code_hash'           => Hash::make($plainCode),
                'attempts'            => 0,
                'expires_at'          => now()->addMinutes(self::OTP_TTL_MINUTES),
                'ip_address'          => $ip,
            ]);
        });

        // 4. Apply saved DB SMTP config (if enabled) and send email
        try {
            MailConfigService::apply();
            Mail::to($provider->email)->send(
                new ProviderPasswordResetOtpMail($provider, $plainCode, self::OTP_TTL_MINUTES)
            );
        } catch (\Throwable $e) {
            // Roll back / invalidate the OTP record so cooldown doesn't trap the user after a transport failure
            $otpRecord->delete();

            Log::error('Provider OTP email delivery failed', [
                'provider_id' => $provider->id,
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => 'تعذر إرسال رمز التحقق إلى بريدك الإلكتروني حالياً. يُرجى التواصل مع الإدارة للتحقق من إعدادات البريد (SMTP).',
            ];
        }

        return [
            'success'      => true,
            'masked_email' => $provider->maskedEmail(),
            'expires_in'   => self::OTP_TTL_MINUTES,
        ];
    }

    /**
     * Verify OTP code and reset provider's password atomically.
     */
    public function verifyAndResetPassword(ServiceProvider $provider, string $code, string $newPassword): array
    {
        $cleanCode = preg_replace('/[^0-9]/', '', trim($code));
        if (strlen($cleanCode) !== 6) {
            return [
                'success' => false,
                'error'   => 'رمز التحقق (OTP) يجب أن يتكون من 6 أرقام.',
            ];
        }

        return DB::transaction(function () use ($provider, $cleanCode, $newPassword) {
            $otp = ProviderPasswordOtp::where('service_provider_id', $provider->id)
                ->whereNull('used_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$otp) {
                return [
                    'success' => false,
                    'error'   => 'لا يوجد رمز تحقق نشط. يُرجى طلب رمز جديد.',
                ];
            }

            if ($otp->isExpired()) {
                $otp->update(['used_at' => now()]);
                return [
                    'success' => false,
                    'error'   => 'انتهت صلاحية رمز التحقق (10 دقائق). يُرجى طلب رمز جديد.',
                ];
            }

            if ($otp->attempts >= self::MAX_ATTEMPTS) {
                $otp->update(['used_at' => now()]);
                return [
                    'success' => false,
                    'error'   => 'تم تجاوز الحد الأقصى لمحاولات إدخال الرمز. يُرجى طلب رمز تحقق جديد.',
                ];
            }

            if (!Hash::check($cleanCode, $otp->code_hash)) {
                $otp->increment('attempts');
                $remaining = max(0, self::MAX_ATTEMPTS - $otp->attempts);

                if ($remaining === 0) {
                    $otp->update(['used_at' => now()]);
                    return [
                        'success' => false,
                        'error'   => 'رمز التحقق غير صحيح وتم إبطال الرمز لتجاوز عدد المحاولات. يُرجى طلب رمز جديد.',
                    ];
                }

                return [
                    'success' => false,
                    'error'   => "رمز التحقق غير صحيح. تبقى لديك {$remaining} محاولات.",
                ];
            }

            // Valid OTP — reset password, rotate remember_token, burn OTP
            $provider->forceFill([
                'password'       => Hash::make($newPassword),
                'remember_token' => Str::random(60),
            ])->save();

            $otp->update(['used_at' => now()]);

            ProviderPasswordOtp::where('service_provider_id', $provider->id)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            return [
                'success' => true,
            ];
        });
    }
}
