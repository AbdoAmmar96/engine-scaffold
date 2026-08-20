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
}
