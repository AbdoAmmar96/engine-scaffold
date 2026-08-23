<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * صحّة الجدولة.
 *
 * على الاستضافة المشتركة الـ cron بيتضاف من لوحة التحكم مش من الكود، يعني
 * ممكن ما يتضافش خالص ومحدش ياخد باله: تنبيهات البحث المحفوظ تفضل واقفة
 * أسابيع والموقع شكله سليم تمامًا. النبضة دي بتحوّل الغياب الصامت ده
 * لتحذير ظاهر على أول شاشة بعد الدخول.
 *
 * ليه ملف مش صف في الداتابيز: بتتكتب كل دقيقة — 1,440 كتابة في اليوم
 * على SQLite مقابل `touch` مجاني تقريبًا، ومن غير ما تلوّث سجل النشاط.
 */
class Scheduler
{
    /**
     * بعدها نعتبر الجدولة واقفة.
     * دقيقتين سماح: الـ cron بيتأخر تحت الحمل، وتحذير بيرمش مع كل تأخيرة
     * تحذير محدش هيصدّقه.
     */
    public const STALE_AFTER = 180;

    /** بتتنادى من الجدولة نفسها كل دقيقة */
    public static function beat(): void
    {
        @touch(self::path());
    }

    public static function lastRun(): ?CarbonImmutable
    {
        $path = self::path();

        // stat بيتكاش داخل نفس الطلب، والملف بيتغيّر من عملية تانية خالص
        clearstatcache(true, $path);

        $time = is_file($path) ? @filemtime($path) : false;

        return $time === false ? null : CarbonImmutable::createFromTimestamp($time);
    }

    /** ثواني من آخر نبضة — `null` يعني الجدولة ما اشتغلتش ولا مرة */
    public static function secondsSinceRun(): ?int
    {
        $last = self::lastRun();

        // طرح تايم ستامب مباشر مش diff: مفيش لبس في إشارة الفرق
        return $last === null ? null : max(0, time() - $last->getTimestamp());
    }

    public static function isHealthy(): bool
    {
        $seconds = self::secondsSinceRun();

        return $seconds !== null && $seconds <= self::STALE_AFTER;
    }

    /**
     * الحالة بالشكل اللي الشاشة بتستهلكه.
     *
     * @return array{healthy: bool, ever_ran: bool, minutes: int|null, command: string}
     */
    public static function status(): array
    {
        $seconds = self::secondsSinceRun();

        return [
            'healthy' => self::isHealthy(),
            'ever_ran' => $seconds !== null,
            'minutes' => $seconds === null ? null : intdiv($seconds, 60),
            'command' => self::cronLine(),
        ];
    }

    /**
     * السطر اللي المدير بينسخه في لوحة التحكم.
     *
     * بيشاور على `cron.sh` مش على `php artisan` مباشرة: مسار الـ PHP بيختلف
     * من استضافة للتانية، والسكريبت بيحلّه لوحده — فالسطر ده ما بيتغيّرش أبدًا.
     */
    public static function cronLine(): string
    {
        return '* * * * * bash '.base_path('cron.sh');
    }

    private static function path(): string
    {
        return storage_path('app/schedule-heartbeat');
    }
}
