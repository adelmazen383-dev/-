<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailConfigService
{
    public const KEY_ENABLED      = 'provider_smtp_enabled';
    public const KEY_HOST         = 'provider_smtp_host';
    public const KEY_PORT         = 'provider_smtp_port';
    public const KEY_ENCRYPTION   = 'provider_smtp_encryption';
    public const KEY_USERNAME     = 'provider_smtp_username';
    public const KEY_PASSWORD_ENC = 'provider_smtp_password_enc';
    public const KEY_FROM_ADDRESS = 'provider_smtp_from_address';
    public const KEY_FROM_NAME    = 'provider_smtp_from_name';

    /**
     * Apply saved DB SMTP configuration at runtime if enabled.
     * Falls back cleanly to .env MAIL_* configuration if not enabled in DB.
     */
    public static function apply(): bool
    {
        if (Setting::get(self::KEY_ENABLED, '0') !== '1') {
            return false;
        }

        $host = trim((string) Setting::get(self::KEY_HOST, ''));
        $port = (int) Setting::get(self::KEY_PORT, 587);
        $fromAddress = trim((string) Setting::get(self::KEY_FROM_ADDRESS, ''));

        if ($host === '' || $port <= 0 || $fromAddress === '') {
            return false;
        }

        $encryption = strtolower(trim((string) Setting::get(self::KEY_ENCRYPTION, 'tls')));
        $username   = (string) Setting::get(self::KEY_USERNAME, '');
        $fromName   = trim((string) Setting::get(self::KEY_FROM_NAME, 'بوابة مقدمي الخدمات'));
        $password   = self::getDecryptedPassword();

        $scheme = match ($encryption) {
            'ssl', 'smtps' => 'smtps',
            default        => 'smtp',
        };

        config([
            'mail.default'                  => 'smtp',
            'mail.mailers.smtp.scheme'      => $scheme,
            'mail.mailers.smtp.host'        => $host,
            'mail.mailers.smtp.port'        => $port,
            'mail.mailers.smtp.username'    => $username !== '' ? $username : null,
            'mail.mailers.smtp.password'    => $password !== '' ? $password : null,
            'mail.mailers.smtp.timeout'     => 15,
            'mail.from.address'             => $fromAddress,
            'mail.from.name'                => $fromName !== '' ? $fromName : 'بوابة مقدمي الخدمات',
        ]);

        try {
            Mail::purge('smtp');
        } catch (\Throwable $e) {
            // Safe ignore when Mail::fake() is active in tests
        }

        return true;
    }

    public static function getDecryptedPassword(): string
    {
        $enc = (string) Setting::get(self::KEY_PASSWORD_ENC, '');
        if ($enc === '') {
            return '';
        }

        try {
            return Crypt::decryptString($enc);
        } catch (\Throwable $e) {
            Log::warning('Failed to decrypt provider SMTP password.');
            return '';
        }
    }

    public static function saveSettings(array $data): void
    {
        Setting::set(self::KEY_ENABLED, !empty($data['smtp_enabled']) ? '1' : '0');
        Setting::set(self::KEY_HOST, trim((string) ($data['smtp_host'] ?? '')));
        Setting::set(self::KEY_PORT, (string) ((int) ($data['smtp_port'] ?? 587)));
        Setting::set(self::KEY_ENCRYPTION, strtolower(trim((string) ($data['smtp_encryption'] ?? 'tls'))));
        Setting::set(self::KEY_USERNAME, trim((string) ($data['smtp_username'] ?? '')));
        Setting::set(self::KEY_FROM_ADDRESS, trim((string) ($data['smtp_from_address'] ?? '')));
        Setting::set(self::KEY_FROM_NAME, trim((string) ($data['smtp_from_name'] ?? 'بوابة مقدمي الخدمات')));

        if (!empty($data['smtp_password'])) {
            Setting::set(self::KEY_PASSWORD_ENC, Crypt::encryptString((string) $data['smtp_password']));
        }
    }

    public static function getSettingsForView(): array
    {
        $dbEnabled = Setting::get(self::KEY_ENABLED, '0') === '1';
        $envMailer = (string) config('mail.default', 'log');

        return [
            'smtp_enabled'      => $dbEnabled,
            'smtp_host'         => (string) Setting::get(self::KEY_HOST, ''),
            'smtp_port'         => (string) Setting::get(self::KEY_PORT, '587'),
            'smtp_encryption'   => (string) Setting::get(self::KEY_ENCRYPTION, 'tls'),
            'smtp_username'     => (string) Setting::get(self::KEY_USERNAME, ''),
            'has_password'      => Setting::get(self::KEY_PASSWORD_ENC, '') !== '',
            'smtp_from_address' => (string) Setting::get(self::KEY_FROM_ADDRESS, ''),
            'smtp_from_name'    => (string) Setting::get(self::KEY_FROM_NAME, 'بوابة مقدمي الخدمات - ثقة'),
            'effective_mailer'  => $dbEnabled ? 'smtp (إعدادات لوحة التحكم)' : $envMailer,
            'is_live_ready'     => $dbEnabled || !in_array($envMailer, ['log', 'array'], true),
        ];
    }
}
