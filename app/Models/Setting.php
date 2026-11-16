<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting by key with a fallback default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $value = Cache::remember("app_setting.{$key}", 3600, function () use ($key) {
                if (!Schema::hasTable('settings')) {
                    return null;
                }
                return static::where('key', $key)->value('value');
            });

            return ($value !== null && $value !== '') ? $value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value): void
    {
        try {
            if (!Schema::hasTable('settings')) {
                return;
            }
            static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
            Cache::forget("app_setting.{$key}");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to save setting [{$key}]: " . $e->getMessage());
        }
    }

    /**
     * Get fee percentage for a specific payment method.
     */
    public static function getFeePercentage(string $method): float
    {
        return match ($method) {
            'apple_pay' => (float) static::get('fee_apple_pay_percent', 5.00),
            'standard'  => (float) static::get('fee_visa_percent', 5.00),
            'tabby'     => (float) static::get('fee_tabby_percent', 15.00),
            'tamara'    => (float) static::get('fee_tamara_percent', 15.00),
            default     => 0.00,
        };
    }
}
