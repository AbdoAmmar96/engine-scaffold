<?php

namespace App\Support;

/**
 * حقول ثنائية اللغة من غير جداول ترجمة:
 * العمود الأساسي عربي، و<name>_en اختياري.
 * ->t('name') بترجّع الإنجليزي لو موجود واللغة en، وإلا بترجع العربي.
 */
trait Bilingual
{
    public function t(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'en') {
            $en = $this->{$field.'_en'} ?? null;

            if (filled($en)) {
                return $en;
            }
        }

        return $this->{$field};
    }

    /** حقل فيه بند في كل سطر (مميزات، صور) → مصفوفة منضّفة */
    public function tLines(string $field, ?string $locale = null): array
    {
        return self::lines($this->t($field, $locale));
    }

    /** بند في كل سطر → مصفوفة، من غير أسطر فاضية */
    public static function lines(?string $text): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text) ?: []),
            fn ($line) => $line !== '',
        ));
    }
}
