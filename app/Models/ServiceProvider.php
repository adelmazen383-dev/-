<?php

namespace App\Models;

use App\Enums\ProviderServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ServiceProvider extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'service_providers';

    protected $fillable = [
        'name',
        'business_name',
        'email',
        'phone',
        'password',
        'service_type',
        'property_kind',
        'custom_service_name',
        'service_description',
        'city',
        'commercial_register',
        'national_id_number',
        'national_id_front_path',
        'national_id_back_path',
        'tourism_license_path',
        'commercial_doc_path',
        'photography_license_path',
        'bank_name',
        'bank_account_name',
        'iban',
        'bank_owner_confirmed',
        'is_active',
        'subscription_status',
        'subscription_starts_at',
        'subscription_ends_at',
        'wallet_balance',
        'total_earned',
        'total_settled',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'               => 'hashed',
            'is_active'              => 'boolean',
            'bank_owner_confirmed'   => 'boolean',
            'subscription_starts_at' => 'datetime',
            'subscription_ends_at'   => 'datetime',
            'last_login_at'          => 'datetime',
            'wallet_balance'         => 'decimal:2',
            'total_earned'           => 'decimal:2',
            'total_settled'          => 'decimal:2',
        ];
    }

    /* ── Relationships ── */

    public function invoices(): HasMany
    {
        return $this->hasMany(ProviderInvoice::class, 'service_provider_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ProviderSubscription::class, 'service_provider_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(ProviderWalletTransaction::class, 'service_provider_id');
    }

    public function passwordOtps(): HasMany
    {
        return $this->hasMany(ProviderPasswordOtp::class, 'service_provider_id');
    }

    /* ── Service Type Helpers ── */

    public function serviceTypeEnum(): ProviderServiceType
    {
        return ProviderServiceType::tryFrom((string) $this->service_type) ?? ProviderServiceType::OTHER;
    }

    public function getServiceTypeLabelAttribute(): string
    {
        $enum = $this->serviceTypeEnum();
        if ($enum === ProviderServiceType::OTHER && !empty($this->custom_service_name)) {
            return $enum->label() . ' — ' . $this->custom_service_name;
        }
        return $enum->label();
    }

    /* ── Subscription Helpers ── */

    public function hasActiveSubscription(): bool
    {
        return $this->is_active
            && $this->subscription_status === 'active'
            && $this->subscription_ends_at !== null
            && $this->subscription_ends_at->isFuture();
    }

    public function effectiveSubscriptionStatus(): string
    {
        if (!$this->is_active || $this->subscription_status === 'suspended') {
            return 'suspended';
        }
        if ($this->subscription_status === 'active' && $this->subscription_ends_at !== null) {
            return $this->subscription_ends_at->isFuture() ? 'active' : 'expired';
        }
        if ($this->subscription_ends_at !== null && $this->subscription_ends_at->isPast()) {
            return 'expired';
        }
        return 'inactive';
    }

    public function subscriptionStatusLabel(): string
    {
        return match ($this->effectiveSubscriptionStatus()) {
            'active'    => 'نشط',
            'expired'   => 'منتهي الاشتراك',
            'suspended' => 'موقوف',
            default     => 'غير مشترك',
        };
    }

    public function subscriptionDaysRemaining(): int
    {
        if (!$this->hasActiveSubscription()) {
            return 0;
        }
        return max(0, (int) now()->diffInDays($this->subscription_ends_at, false));
    }

    /* ── Uploaded KYC Documents Helper ── */

    public function uploadedDocuments(): array
    {
        $map = [
            'national_id_front'   => ['label' => 'الهوية الوطنية (الوجه الأمامي)', 'path' => $this->national_id_front_path],
            'national_id_back'    => ['label' => 'الهوية الوطنية (الوجه الخلفي)', 'path' => $this->national_id_back_path],
            'tourism_license'     => ['label' => 'رخصة السياحة', 'path' => $this->tourism_license_path],
            'commercial_doc'      => ['label' => 'وثيقة العمل الحر / السجل التجاري', 'path' => $this->commercial_doc_path],
            'photography_license' => ['label' => 'ترخيص التصوير', 'path' => $this->photography_license_path],
        ];

        $out = [];
        foreach ($map as $key => $info) {
            if (!empty($info['path'])) {
                $info['is_image'] = \App\Services\ProviderDocumentService::isImage($info['path']);
                $info['is_pdf']   = \App\Services\ProviderDocumentService::isPdf($info['path']);
                $out[$key] = $info;
            }
        }
        return $out;
    }

    public function documentPathForKey(string $key): ?string
    {
        return match ($key) {
            'national_id_front'   => $this->national_id_front_path,
            'national_id_back'    => $this->national_id_back_path,
            'tourism_license'     => $this->tourism_license_path,
            'commercial_doc'      => $this->commercial_doc_path,
            'photography_license' => $this->photography_license_path,
            default               => null,
        };
    }

    /* ── Privacy / Display Helpers ── */

    public function maskedEmail(): string
    {
        $email = (string) $this->email;
        if (!str_contains($email, '@')) {
            return '***';
        }
        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));
        return $visible . str_repeat('*', max(3, mb_strlen($local) - 2)) . '@' . $domain;
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($digits, '00966')) {
            $digits = '0' . substr($digits, 5);
        } elseif (str_starts_with($digits, '966') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 3);
        } elseif (str_starts_with($digits, '5') && strlen($digits) === 9) {
            $digits = '0' . $digits;
        }
        return $digits;
    }
}
