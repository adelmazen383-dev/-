<?php

namespace App\Services;

use App\Models\ServiceProvider;
use App\Models\Setting;

class ProviderSubscriptionConfigService
{
    public const PLAN_1M_ENABLED  = 'provider_plan_1m_enabled';
    public const PLAN_1M_PRICE    = 'provider_plan_1m_price';
    public const PLAN_1M_LABEL    = 'provider_plan_1m_label';

    public const PLAN_3M_ENABLED  = 'provider_plan_3m_enabled';
    public const PLAN_3M_PRICE    = 'provider_plan_3m_price';
    public const PLAN_3M_LABEL    = 'provider_plan_3m_label';

    public const PLAN_6M_ENABLED  = 'provider_plan_6m_enabled';
    public const PLAN_6M_PRICE    = 'provider_plan_6m_price';
    public const PLAN_6M_LABEL    = 'provider_plan_6m_label';

    public const PLAN_12M_ENABLED = 'provider_plan_12m_enabled';
    public const PLAN_12M_PRICE   = 'provider_plan_12m_price';
    public const PLAN_12M_LABEL   = 'provider_plan_12m_label';

    public const FEATURES_TEXT    = 'provider_sub_features';
    public const WHATSAPP_NUMBER  = 'provider_sub_whatsapp';
    public const PHONE_NUMBER     = 'provider_sub_phone';
    public const CONTACT_EMAIL    = 'provider_sub_email';
    public const BANK_NAME        = 'provider_sub_bank_name';
    public const BANK_ACCOUNT     = 'provider_sub_bank_account';
    public const BANK_IBAN        = 'provider_sub_iban';
    public const CONTACT_NOTE     = 'provider_sub_contact_note';

    public static function getConfig(): array
    {
        $defaultFeatures = implode("\n", [
            'إصدار فواتير إلكترونية غير محدودة طوال فترة الاشتراك',
            'تفعيل الدفع عبر Apple Pay ومدى وفيزا وتابي وتمارا',
            'إضافة مبالغ الفواتير المدفوعة تلقائياً إلى محفظتك',
            'تحويل مستحقات المحفظة إلى حسابك البنكي بانتظام',
        ]);

        return [
            'plans' => [
                1 => [
                    'months'  => 1,
                    'enabled' => Setting::get(self::PLAN_1M_ENABLED, '1') === '1',
                    'price'   => (float) Setting::get(self::PLAN_1M_PRICE, 199.00),
                    'label'   => (string) Setting::get(self::PLAN_1M_LABEL, 'الباقة الشهرية (شهر واحد)'),
                ],
                3 => [
                    'months'  => 3,
                    'enabled' => Setting::get(self::PLAN_3M_ENABLED, '1') === '1',
                    'price'   => (float) Setting::get(self::PLAN_3M_PRICE, 549.00),
                    'label'   => (string) Setting::get(self::PLAN_3M_LABEL, 'باقة 3 أشهر'),
                ],
                6 => [
                    'months'  => 6,
                    'enabled' => Setting::get(self::PLAN_6M_ENABLED, '0') === '1',
                    'price'   => (float) Setting::get(self::PLAN_6M_PRICE, 999.00),
                    'label'   => (string) Setting::get(self::PLAN_6M_LABEL, 'باقة 6 أشهر'),
                ],
                12 => [
                    'months'  => 12,
                    'enabled' => Setting::get(self::PLAN_12M_ENABLED, '0') === '1',
                    'price'   => (float) Setting::get(self::PLAN_12M_PRICE, 1799.00),
                    'label'   => (string) Setting::get(self::PLAN_12M_LABEL, 'الباقة السنوية (12 شهراً)'),
                ],
            ],
            'features_text' => (string) Setting::get(self::FEATURES_TEXT, $defaultFeatures),
            'features_list' => array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', (string) Setting::get(self::FEATURES_TEXT, $defaultFeatures)) ?: [])
            )),
            'whatsapp'      => trim((string) Setting::get(self::WHATSAPP_NUMBER, '')),
            'phone'         => trim((string) Setting::get(self::PHONE_NUMBER, '')),
            'email'         => trim((string) Setting::get(self::CONTACT_EMAIL, '')),
            'bank_name'     => trim((string) Setting::get(self::BANK_NAME, '')),
            'bank_account'  => trim((string) Setting::get(self::BANK_ACCOUNT, '')),
            'bank_iban'     => trim((string) Setting::get(self::BANK_IBAN, '')),
            'contact_note'  => trim((string) Setting::get(self::CONTACT_NOTE, 'تواصل مع الإدارة لتفعيل أو تجديد اشتراكك الشهري وفتح خدمة إصدار الفواتير فوراً.')),
        ];
    }

    public static function saveConfig(array $data): void
    {
        Setting::set(self::PLAN_1M_ENABLED, !empty($data['plan_1m_enabled']) ? '1' : '0');
        Setting::set(self::PLAN_1M_PRICE, number_format((float) ($data['plan_1m_price'] ?? 0), 2, '.', ''));
        Setting::set(self::PLAN_1M_LABEL, trim((string) ($data['plan_1m_label'] ?? 'الباقة الشهرية (شهر واحد)')));

        Setting::set(self::PLAN_3M_ENABLED, !empty($data['plan_3m_enabled']) ? '1' : '0');
        Setting::set(self::PLAN_3M_PRICE, number_format((float) ($data['plan_3m_price'] ?? 0), 2, '.', ''));
        Setting::set(self::PLAN_3M_LABEL, trim((string) ($data['plan_3m_label'] ?? 'باقة 3 أشهر')));

        Setting::set(self::PLAN_6M_ENABLED, !empty($data['plan_6m_enabled']) ? '1' : '0');
        Setting::set(self::PLAN_6M_PRICE, number_format((float) ($data['plan_6m_price'] ?? 0), 2, '.', ''));
        Setting::set(self::PLAN_6M_LABEL, trim((string) ($data['plan_6m_label'] ?? 'باقة 6 أشهر')));

        Setting::set(self::PLAN_12M_ENABLED, !empty($data['plan_12m_enabled']) ? '1' : '0');
        Setting::set(self::PLAN_12M_PRICE, number_format((float) ($data['plan_12m_price'] ?? 0), 2, '.', ''));
        Setting::set(self::PLAN_12M_LABEL, trim((string) ($data['plan_12m_label'] ?? 'الباقة السنوية (12 شهراً)')));

        Setting::set(self::FEATURES_TEXT, trim((string) ($data['features_text'] ?? '')));
        Setting::set(self::WHATSAPP_NUMBER, trim((string) ($data['whatsapp'] ?? '')));
        Setting::set(self::PHONE_NUMBER, trim((string) ($data['phone'] ?? '')));
        Setting::set(self::CONTACT_EMAIL, trim((string) ($data['email'] ?? '')));
        Setting::set(self::BANK_NAME, trim((string) ($data['bank_name'] ?? '')));
        Setting::set(self::BANK_ACCOUNT, trim((string) ($data['bank_account'] ?? '')));
        Setting::set(self::BANK_IBAN, strtoupper(preg_replace('/\s+/', '', (string) ($data['bank_iban'] ?? ''))));
        Setting::set(self::CONTACT_NOTE, trim((string) ($data['contact_note'] ?? '')));
    }

    public static function formatWhatsAppDigits(string $raw): string
    {
        $digits = preg_replace('/[^0-9]/', '', $raw);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '05') && strlen($digits) === 10) {
            $digits = '966' . substr($digits, 1);
        } elseif (str_starts_with($digits, '5') && strlen($digits) === 9) {
            $digits = '966' . $digits;
        }
        return $digits;
    }

    public static function buildSubscribeWhatsAppUrl(ServiceProvider $provider, ?array $plan = null): ?string
    {
        $cfg = self::getConfig();
        $digits = self::formatWhatsAppDigits($cfg['whatsapp']);
        if ($digits === '') {
            return null;
        }

        $lines = [
            'السلام عليكم، أرغب في تفعيل / تجديد الاشتراك الشهري في بوابة مقدمي الخدمات — منصة ثقة:',
            'اسم النشاط: ' . $provider->business_name,
            'الاسم: ' . $provider->name,
            'نوع الخدمة: ' . $provider->service_type_label,
            'رقم الجوال: ' . $provider->phone,
            'البريد الإلكتروني: ' . $provider->email,
        ];

        if ($plan) {
            $lines[] = 'الباقة المطلوبة: ' . $plan['label'] . ' (' . number_format((float) $plan['price'], 2) . ' ر.س)';
        }

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode(implode("\n", $lines));
    }

    public static function buildEditRequestWhatsAppUrl(ServiceProvider $provider): ?string
    {
        $cfg = self::getConfig();
        $digits = self::formatWhatsAppDigits($cfg['whatsapp']);
        if ($digits === '') {
            return null;
        }

        $text = implode("\n", [
            'السلام عليكم، أرغب في طلب تعديل بيانات حسابي في بوابة مقدمي الخدمات — منصة ثقة:',
            'اسم النشاط: ' . $provider->business_name,
            'رقم الجوال: ' . $provider->phone,
            'البريد الإلكتروني: ' . $provider->email,
            'التعديل المطلوب: ',
        ]);

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($text);
    }
}
