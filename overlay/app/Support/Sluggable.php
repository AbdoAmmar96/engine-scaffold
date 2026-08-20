<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * رابط ثابت فريد لصفحة العرض. الإنجليزي أولى عشان الرابط يفضل ASCII،
 * ولو مفيش إنجليزي بيستخدم العربي كما هو (Str::slug بـ null بتسيب الحروف العربية).
 */
trait Sluggable
{
    /** الكلمة الاحتياطية لو العنوان مالوش أحرف صالحة */
    protected static function slugFallback(): string
    {
        return 'item';
    }

    public static function buildSlug(string $base, ?string $baseEn = null, ?int $ignoreId = null): string
    {
        $root = filled($baseEn) ? Str::slug($baseEn) : Str::slug($base, '-', null);
        $root = $root !== '' ? $root : static::slugFallback();

        $slug = $root;
        $i = 2;

        while (static::query()->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $root.'-'.$i++;
        }

        return $slug;
    }
}
