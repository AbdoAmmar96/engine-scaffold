<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * المقاطع المحجوزة تحت بادئة اللغة.
 *
 * صفحات المحتوى بتعيش على `/{locale}/{slug}` مباشرة عشان الرابط يفضل نضيف
 * (`/ar/privacy-policy` مش `/ar/pages/privacy-policy`). الثمن إنها بتتشارك
 * المساحة دي مع كل راوت من مقطع واحد في الموقع: صفحة اسمها «اتصل بنا»
 * هتولّد slug اسمه `contact` وتقعد ورا الراوت الموجود من غير ما تشتكي —
 * الأدمن هيحفظ ويشوف صفحة تانية خالص ومش هيفهم ليه.
 *
 * القايمة **مشتقّة من الراوتر مش مكتوبة بالإيد**: أي راوت جديد بيتسجّل
 * بيتحجز مقطعه تلقائيًا. قايمة مكتوبة كانت هتتأخّر عن الراوتات أول ما حد
 * يضيف مسار جديد، والعطل هيظهر كصفحة مش بتفتح مش كخطأ.
 */
class ReservedSlugs
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $reserved = [];

        // ->getRoutes() بترجّع مصفوفة صريحة — الـ collection نفسها
        // مش معرّفة كـ iterable في تعريفات النوع
        foreach (Route::getRoutes()->getRoutes() as $route) {
            $uri = $route->uri();

            if (! str_starts_with($uri, '{locale}/')) {
                continue;
            }

            $segment = explode('/', substr($uri, strlen('{locale}/')))[0];

            // المتغيّرات مش مقاطع ثابتة — `{slug}` بتاع الصفحات نفسه من دول
            if ($segment === '' || str_contains($segment, '{')) {
                continue;
            }

            $reserved[$segment] = true;
        }

        // مسارات مش راوتات جوّه مجموعة اللغة، بس لو صفحة خدت اسمها
        // الرابط بيبقى ملخبط على الأقل
        foreach (['admin', 'api', 'storage', 'build'] as $extra) {
            $reserved[$extra] = true;
        }

        ksort($reserved);

        return array_keys($reserved);
    }

    public static function taken(string $slug): bool
    {
        return in_array(strtolower(trim($slug)), self::all(), true);
    }
}
