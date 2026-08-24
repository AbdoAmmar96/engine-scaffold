<?php

namespace App\Support;

use Modules\Core\Services\SettingsService;

/**
 * أقسام الموقع اللي بتتقفل من الداشبورد.
 *
 * القسم المقفول لازم يختفي من **أربع** حتت مع بعض، وإلا بيفضل موجود
 * بشكل تاني: الراوت بيرجّع 404، والقايمة بتشيل لينكه، وخريطة الموقع
 * مبتذكرهوش، واللوحة بتقول للأدمن إنه مقفول عشان ما ينشرش في الفراغ.
 * ناقص واحدة منهم = جوجل بيفهرس صفحة الزائر مش شايفها، أو الأدمن
 * بيكتب مقال ويستنى ظهوره من غير ما حد يقوله إن القسم مطفي.
 *
 * الافتراضي **مقفول**: التثبيت الجديد ما يطلعش قسم فاضي، والمنصّة
 * اللي مالهاش محتوى بتفتحه لما يبقى عندها كلام تقوله.
 */
class Features
{
    /**
     * المفتاح = اسم القسم · `key` = مفتاح الإعداد في مجموعة general
     * · `paths` = بادئات المسارات اللي بتختفي معاه (من غير بادئة اللغة).
     *
     * @var array<string, array{key: string, paths: list<string>}>
     */
    public const TOGGLES = [
        'blog' => ['key' => 'blog_enabled', 'paths' => ['/blog']],
    ];

    public static function enabled(string $feature): bool
    {
        $toggle = self::TOGGLES[$feature] ?? null;

        if ($toggle === null) {
            return true;
        }

        // '0' هو الافتراضي لما الصف مش موجود — التثبيت القديم بيقفل القسم
        // لحد ما الأدمن يفتحه بنفسه، مش العكس
        return (string) app(SettingsService::class)->get('general', $toggle['key'], '0') === '1';
    }

    /**
     * بادئات المسارات المخفية دلوقتي — القايمة وخريطة الموقع بيفلتروا بيها.
     *
     * @return list<string>
     */
    public static function hiddenPaths(): array
    {
        $hidden = [];

        foreach (self::TOGGLES as $feature => $toggle) {
            if (! self::enabled($feature)) {
                $hidden = [...$hidden, ...$toggle['paths']];
            }
        }

        return $hidden;
    }

    /** المسار ده تبع قسم مقفول؟ بيقارن البادئة عشان /blog/{slug} يتشال كمان */
    public static function hidden(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        // اللينك ممكن يكون فيه بادئة لغة (/ar/blog) أو من غيرها (/blog)
        $path = preg_replace('#^/(ar|en)(?=/|$)#', '', $path) ?? $path;

        foreach (self::hiddenPaths() as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return true;
            }
        }

        return false;
    }
}
