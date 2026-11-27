<?php

namespace App\Enums;

enum ProviderServiceType: string
{
    case COORDINATION    = 'coordination';
    case PHOTOGRAPHY     = 'photography';
    case SERVICED_SUITES = 'serviced_suites';
    case OTHER           = 'other';

    public function label(): string
    {
        return match ($this) {
            self::COORDINATION    => 'تنسيق',
            self::PHOTOGRAPHY     => 'تصوير',
            self::SERVICED_SUITES => 'أجنحة فندقية مخدومة',
            self::OTHER           => 'أخرى',
        };
    }

    public function subtitle(): string
    {
        return match ($this) {
            self::COORDINATION    => 'تنسيق حفلات ومناسبات وتجهيز الغرف والفعاليات',
            self::PHOTOGRAPHY     => 'تصوير فوتوغرافي وفيديو وجلسات تصوير احترافية',
            self::SERVICED_SUITES => 'شقة، فيلا، شاليه، أو أجنحة فندقية مخدومة (عقار)',
            self::OTHER           => 'خدمات أخرى متنوعة (مع كتابة تفاصيل الخدمة)',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::COORDINATION    => '🎀',
            self::PHOTOGRAPHY     => '📸',
            self::SERVICED_SUITES => '🏨',
            self::OTHER           => '✨',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::COORDINATION    => 'bg-fuchsia-100 text-fuchsia-800 border-fuchsia-200',
            self::PHOTOGRAPHY     => 'bg-amber-100 text-amber-800 border-amber-200',
            self::SERVICED_SUITES => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::OTHER           => 'bg-sky-100 text-sky-800 border-sky-200',
        };
    }

    public function templateView(): string
    {
        return 'provider.invoices.templates.' . $this->value;
    }

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = [
                'value'    => $case->value,
                'label'    => $case->label(),
                'subtitle' => $case->subtitle(),
                'icon'     => $case->icon(),
            ];
        }
        return $out;
    }
}
