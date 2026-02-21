<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function adminEmail(): string
    {
        return static::get('admin_notification_email', config('app.nada_admin_email', 'admin@acudetox.com'));
    }

    public static function imageOptimizationEnabled(): bool
    {
        return (bool) static::get('image_optimization_enabled', '1');
    }

    public static function imageWebpQuality(): int
    {
        return (int) static::get('image_webp_quality', '80');
    }

    public static function imageMaxWidth(): int
    {
        return (int) static::get('image_max_width', '1920');
    }

    public static function imageMaxHeight(): int
    {
        return (int) static::get('image_max_height', '1920');
    }

    public static function imageThumbSize(): int
    {
        return (int) static::get('image_thumb_size', '400');
    }

    public static function umamiEnabled(): bool
    {
        return (bool) static::get('umami_enabled', '0');
    }

    public static function umamiScriptUrl(): string
    {
        return static::get('umami_script_url', '') ?? '';
    }

    public static function umamiWebsiteId(): string
    {
        return static::get('umami_website_id', '') ?? '';
    }

    public static function getJson(string $key, array $default = []): array
    {
        $value = static::get($key);

        return $value ? json_decode($value, true) : $default;
    }

    public static function setJson(string $key, array $value): void
    {
        static::set($key, json_encode($value));
    }

    public static function clinicalHoursThreshold(): float
    {
        return (float) static::get('clinical_hours_threshold', '40');
    }

    public static function stateLawLink(?string $state): ?string
    {
        if (! $state) {
            return null;
        }

        $links = static::getJson('state_law_links');

        return $links[strtoupper($state)] ?? null;
    }
}
