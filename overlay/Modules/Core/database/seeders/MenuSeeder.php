<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\MenuItem;

/**
 * القائمة الافتراضية — نفس اللينكات اللي كانت مكتوبة في SiteLayout.
 * idempotent: المفتاح هو (المكان + الرابط).
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $header = [
            ['label' => 'الرئيسية',     'label_en' => 'Home',       'url' => '/'],
            ['label' => 'العقارات',     'label_en' => 'Properties', 'url' => '/properties'],
            ['label' => 'عقارات تجارية', 'label_en' => 'Commercial', 'url' => '/properties/commercial'],
            ['label' => 'الكمبوندات',   'label_en' => 'Compounds',  'url' => '/compounds'],
            ['label' => 'المطوّرون',     'label_en' => 'Developers', 'url' => '/developers'],
            ['label' => 'المناطق',      'label_en' => 'Areas',      'url' => '/areas'],
            ['label' => 'المدونة',      'label_en' => 'Blog',       'url' => '/blog'],
            ['label' => 'من نحن',       'label_en' => 'About',      'url' => '/about'],
            ['label' => 'اتصل بنا',     'label_en' => 'Contact',    'url' => '/contact'],
        ];

        foreach ($header as $i => $item) {
            MenuItem::updateOrCreate(
                ['location' => 'header', 'url' => $item['url']],
                $item + ['location' => 'header', 'sort' => $i, 'is_active' => true],
            );
        }

        // الفوتر بياخد نفس اللينكات ما عدا الرئيسية
        foreach (array_slice($header, 1) as $i => $item) {
            MenuItem::updateOrCreate(
                ['location' => 'footer', 'url' => $item['url']],
                $item + ['location' => 'footer', 'sort' => $i, 'is_active' => true],
            );
        }

        $this->command?->info(sprintf('  لينكات القوائم: %d', MenuItem::count()));
    }
}
