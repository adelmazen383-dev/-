<?php

namespace App\Http\Requests\Auth;

use App\Models\ServiceProvider;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'يرجى إدخال البريد الإلكتروني.',
            'email.email'       => 'يرجى إدخال بريد إلكتروني صحيح (تسجيل الدخول متاح بالبريد الإلكتروني فقط).',
            'password.required' => 'يرجى إدخال كلمة المرور.',
        ];
    }

    /**
     * Attempt to authenticate against the web (Admin/Staff) guard only.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->authenticateUnified();
    }

    /**
     * Unified authentication: tries Admin/Staff (web guard) first, then Service Provider (provider guard).
     * Both use Email + Password strictly and remain isolated across guards.
     *
     * @return 'web'|'provider'
     * @throws ValidationException
     */
    public function authenticateUnified(): string
    {
        $this->ensureIsNotRateLimited();

        $email    = strtolower(trim((string) $this->input('email')));
        $password = (string) $this->input('password');
        $remember = $this->boolean('remember');

        // 1. Try Admin / Staff account on `web` guard
        if (Auth::guard('web')->attempt(['email' => $email, 'password' => $password], $remember)) {
            if (! Auth::guard('web')->user()->is_active) {
                Auth::guard('web')->logout();
                throw ValidationException::withMessages([
                    'email' => 'تم تعطيل حسابك من قبل الإدارة. يرجى مراجعة مدير النظام.',
                ]);
            }

            RateLimiter::clear($this->throttleKey());
            return 'web';
        }

        // 2. Try Service Provider account on isolated `provider` guard (Email ONLY)
        $provider = ServiceProvider::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($provider && Hash::check($password, $provider->password)) {
            if (! $provider->is_active) {
                throw ValidationException::withMessages([
                    'email' => 'تم إيقاف هذا الحساب مؤقتاً. يُرجى التواصل مع الإدارة.',
                ]);
            }

            Auth::guard('provider')->login($provider, $remember);
            $provider->forceFill(['last_login_at' => now()])->save();

            RateLimiter::clear($this->throttleKey());
            return 'provider';
        }

        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => 'بيانات تسجيل الدخول غير صحيحة. تأكد من البريد الإلكتروني وكلمة المرور.',
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Log suspicious brute-force attempt
        \Illuminate\Support\Facades\Log::warning('BRUTE FORCE ATTEMPT DETECTED', [
            'ip'      => $this->ip(),
            'email'   => $this->string('email'),
            'seconds' => $seconds,
        ]);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
