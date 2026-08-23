<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\MenuItem;

/**
 * القائمة الافتراضية.
 * idempotent: المفتاح هو (المكان + الاسم) — مش الرابط، لأن عنصر القائمة
 * المنسدلة نفسه مالوش رابط.
 *
 * firstOrCreate مش updateOrCreate: الديبلوي بيشغّل db:seed كل مرة، والتاني
 * كان بيرجّع أي تعديل العميل عمله من /admin/menus — يعني الشاشة بلا فايدة.
 * الناقص بس هو اللي بيتزرع.
 */
class MenuSeeder extends Seeder
{
    /** الهيدر: عنصر ممكن يجيب معاه children */
    private const HEADER = [
        ['label' => 'الرئيسية', 'label_en' => 'Home', 'url' => '/'],
        ['label' => 'خدماتنا', 'label_en' => 'Our services', 'url' => null, 'children' => [
            ['label' => 'العقارات', 'label_en' => 'Properties', 'url' => '/properties'],
            ['label' => 'عقارات تجارية', 'label_en' => 'Commercial', 'url' => '/properties/commercial'],
            ['label' => 'الكمبوندات', 'label_en' => 'Compounds', 'url' => '/compounds'],
            ['label' => 'أضف عقارك', 'label_en' => 'Add your property', 'url' => '/add-property'],
        ]],
        ['label' => 'المطوّرون', 'label_en' => 'Developers', 'url' => '/developers'],
        ['label' => 'المناطق', 'label_en' => 'Areas', 'url' => '/areas'],
        ['label' => 'المدونة', 'label_en' => 'Blog', 'url' => '/blog'],
        ['label' => 'من نحن', 'label_en' => 'About', 'url' => '/about'],
        ['label' => 'اتصل بنا', 'label_en' => 'Contact', 'url' => '/contact'],
    ];

    /** الفوتر مسطّح — القوايم المنسدلة مالهاش لازمة هناك */
    private const FOOTER = [
        ['label' => 'العقارات', 'label_en' => 'Properties', 'url' => '/properties'],
        ['label' => 'عقارات تجارية', 'label_en' => 'Commercial', 'url' => '/properties/commercial'],
        ['label' => 'الكمبوندات', 'label_en' => 'Compounds', 'url' => '/compounds'],
        ['label' => 'المطوّرون', 'label_en' => 'Developers', 'url' => '/developers'],
        ['label' => 'المناطق', 'label_en' => 'Areas', 'url' => '/areas'],
        ['label' => 'المدونة', 'label_en' => 'Blog', 'url' => '/blog'],
        ['label' => 'أضف عقارك', 'label_en' => 'Add your property', 'url' => '/add-property'],
        ['label' => 'من نحن', 'label_en' => 'About', 'url' => '/about'],
        ['label' => 'اتصل بنا', 'label_en' => 'Contact', 'url' => '/contact'],
    ];

    public function run(): void
    {
        foreach (self::HEADER as $i => $item) {
            $parent = $this->put('header', $item, $i, null);

            foreach ($item['children'] ?? [] as $j => $child) {
                $this->put('header', $child, $j, $parent->id);
            }
        }

        foreach (self::FOOTER as $i => $item) {
            $this->put('footer', $item, $i, null);
        }

        MenuItem::flush();

        $this->command?->info(sprintf('  روابط القوائم: %d', MenuItem::count()));
    }

    private function put(string $location, array $item, int $sort, ?int $parentId): MenuItem
    {
        return MenuItem::firstOrCreate(
            ['location' => $location, 'label' => $item['label']],
            [
                'label_en' => $item['label_en'],
                'url' => $item['url'],
                'parent_id' => $parentId,
                'sort' => $sort,
                'is_active' => true,
            ],
        );
    }
}
