<?php

use App\Support\Scheduler;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 |----------------------------------------------------------------------
 | الجدولة
 |----------------------------------------------------------------------
 |
 | محتاجة سطر cron واحد على السيرفر بينده `cron.sh` كل دقيقة — من غيره
 | كل اللي تحت ده متسجّل ومش بيشتغل. الحالة بتتعرض في لوحة التحكم عشان
 | الغياب ما يفضلش صامت (App\Support\Scheduler).
 */

// نبضة: أول حاجة عشان لو أمر تحته وقع، الدليل إن الجدولة نفسها شغّالة يفضل موجود
Schedule::call(fn () => Scheduler::beat())->everyMinute()->name('heartbeat');

// تنبيهات البحث المحفوظ — مرة في اليوم الصبح.
// التوقيت بتوقيت القاهرة عشان الرسالة توصل في وقت معقول للعميل.
// withoutOverlapping: البريد بيتبعت داخل الأمر نفسه (مفيش طابور)، فتشغيلة
// تقيلة ممكن تتعدّى الدقيقة وتلاقي التانية بدأت.
Schedule::command('searches:alert')
    ->dailyAt('09:00')
    ->timezone('Africa/Cairo')
    ->withoutOverlapping();

// صفحات الهبوط البرمجية بتتحدّث أعدادها وبتقفل اللي وحداته خلصت
Schedule::command('seo:landing-pages')
    ->weeklyOn(1, '03:00')
    ->timezone('Africa/Cairo')
    ->withoutOverlapping();
