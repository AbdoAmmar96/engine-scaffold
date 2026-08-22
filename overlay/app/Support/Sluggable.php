<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * رابط ثابت فريد لصفحة العرض. الإنجليزي أولى عشان الرابط يفضل ASCII،
 * ولو مفيش إنجليزي بيستخدم العربي كما هو (Str::slug بـ null بتسيب الحروف العربية).
 */
trait Sluggable
{
    /**
     * ضمان أخير: أي صف بيتحفظ من غير رابط بياخد واحد مولّد.
     * الكنترولرز بتعمل ده أصلًا، بس السيدرز والأوامر والاستيراد مش لازم
     * تفتكر — وصف بلا slug صفحته 404.
     */
    protected static function bootSluggable(): void
    {
        static::saving(function (self $model) {
            if (blank($model->slug)) {
                [$base, $baseEn] = $model->slugSource();

                $model->slug = static::buildSlug($base, $baseEn, $model->exists ? (int) $model->getKey() : null);
            }
        });
    }

    /**
     * العمود اللي الرابط بيتبني منه — الاسم أو العنوان حسب الموديل.
     *
     * @return array{0: string, 1: ?string}
     */
    protected function slugSource(): array
    {
        return [
            (string) ($this->name ?? $this->title ?? ''),
            $this->name_en ?? $this->title_en ?? null,
        ];
    }

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
