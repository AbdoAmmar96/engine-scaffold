<?php

namespace Modules\Seo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * صفحات الهبوط بتتولّد من الوحدات، فمكانها بعد كتالوج العقارات.
 * الأمر نفسه idempotent — تشغيله في كل ديبلوي بيحدّث الأعداد
 * وبيقفل الصفحات اللي وحداتها خلصت، من غير ما يلمس نصوص المحرّر.
 */
class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('seo:landing-pages');

        $this->command->info('  '.trim(Artisan::output()));
    }
}
