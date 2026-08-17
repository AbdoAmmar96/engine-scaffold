<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Models\Setting;

/**
 * قلب الـ Theme Engine:
 * - group('theme')  → توكنز الألوان اللي بتتحقن في app.blade.php
 * - public()        → المجموعات العامة اللي بتتشارك مع React عبر Inertia
 * كل القراءات cached للأبد وبتتمسح تلقائيًا عند أي حفظ.
 */
class SettingsService
{
    public function group(string $group): array
    {
        return Cache::rememberForever(
            "settings.group.{$group}",
            fn () => Setting::query()
                ->where('group', $group)
                ->pluck('value', 'key')
                ->toArray()
        );
    }

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        return $this->group($group)[$key] ?? $default;
    }

    /** المجموعات العامة فقط (is_public) — دي اللي بتوصل للمتصفح */
    public function public(): array
    {
        return Cache::rememberForever(
            'settings.public',
            fn () => Setting::query()
                ->where('is_public', true)
                ->get()
                ->groupBy('group')
                ->map(fn ($rows) => $rows->pluck('value', 'key')->toArray())
                ->toArray()
        );
    }

    public function set(string $group, string $key, mixed $value, bool $isPublic = true): void
    {
        Setting::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value, 'is_public' => $isPublic]
        );

        $this->flush($group);
    }

    public function setMany(string $group, array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['value' => $value]
            );
        }

        $this->flush($group);
    }

    public function flush(?string $group = null): void
    {
        if ($group) {
            Cache::forget("settings.group.{$group}");
        }

        Cache::forget('settings.public');
    }
}
