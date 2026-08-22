<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * العقارات وصفحات الهبوط بيتشاركوا نفس المسار: /{locale}/properties/{slug}.
 *
 * يعني التفرّد لازم يكون على الجدولين مع بعض مش على جدول واحد — لو وحدة
 * أخدت رابط صفحة هبوط، الصفحة بتتحجب (الحل بيدوّر على الهبوط الأول)،
 * ولو صفحة هبوط أخدت رابط وحدة، الوحدة بتختفي من الموقع خالص.
 *
 * بيشتغل على مستوى الجدول مش الموديل عشان موديول Properties وموديول Seo
 * مايستوردوش بعض — الاعتماد بينهم كان هيبقى دايري.
 */
class SharedSlugSpace
{
    /** الجداول اللي بتنشر صفحاتها تحت /properties/ */
    public const TABLES = ['properties', 'seo_landing_pages'];

    public static function taken(string $slug, string $ownTable, ?int $ignoreId = null): bool
    {
        foreach (self::TABLES as $table) {
            // الجدول ممكن يكون لسه ماتعملش — الميجريشن بيشغّل السيدرز في النص
            if (! Schema::hasTable($table)) {
                continue;
            }

            $query = DB::table($table)->where('slug', $slug);

            if ($table === $ownTable && $ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            if ($query->exists()) {
                return true;
            }
        }

        return false;
    }
}
