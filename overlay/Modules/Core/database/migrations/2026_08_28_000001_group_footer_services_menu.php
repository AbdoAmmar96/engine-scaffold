<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ينقل لينكات الخدمات في الفوتر تحت مجموعة «خدماتنا».
 *
 * ليه ميجريشن مش سيدر: ده **تحويل لمرة واحدة** لبيانات موجودة، مش قيمة
 * افتراضية. السيدر بيشتغل في كل رفعة، فلو النقل اتحط فيه كان هيرجّع اللينك
 * للمجموعة كل مرة — يعني الأدمن اللي بيطلّعه بره عن قصد يلاقيه راجع بعد
 * أول deploy. الميجريشن بيشتغل مرة واحدة على العمر، وده بالظبط المطلوب.
 *
 * التثبيت الجديد: الجدول فاضي فالميجريشن مبيعملش حاجة، والسيدر بيبني
 * التركيبة كاملة بعده.
 */
return new class extends Migration
{
    private const GROUP = ['label' => 'خدماتنا', 'label_en' => 'Our services'];

    /** بيتنقلوا بالاسم **والوجهة**: لينك الأدمن غيّر وجهته مش لينكنا */
    private const CHILDREN = [
        'العقارات' => '/properties',
        'عقارات تجارية' => '/properties/commercial',
        'الكمبوندات' => '/compounds',
        'المطوّرون' => '/developers',
    ];

    public function up(): void
    {
        $footer = DB::table('menu_items')->where('location', 'footer');

        // مفيش فوتر أصلًا (تثبيت جديد) — السيدر هو اللي هيبنيه
        if ((clone $footer)->count() === 0) {
            return;
        }

        // المجموعة موجودة خلاص — يبقى النقل اتعمل قبل كده
        if ((clone $footer)->where('label', self::GROUP['label'])->exists()) {
            return;
        }

        $moving = (clone $footer)
            ->whereNull('parent_id')
            ->whereIn('label', array_keys(self::CHILDREN))
            ->get(['id', 'label', 'url']);

        $moving = $moving->filter(fn ($row) => (self::CHILDREN[$row->label] ?? null) === $row->url);

        if ($moving->isEmpty()) {
            return;
        }

        $parentId = DB::table('menu_items')->insertGetId([
            'location' => 'footer',
            'label' => self::GROUP['label'],
            'label_en' => self::GROUP['label_en'],
            'url' => null,
            'parent_id' => null,
            // فوق الباقي: الخدمات أهم لينكات الفوتر
            'sort' => ((clone $footer)->min('sort') ?? 0) - 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (array_keys(self::CHILDREN) as $sort => $label) {
            $row = $moving->firstWhere('label', $label);

            if ($row) {
                DB::table('menu_items')->where('id', $row->id)
                    ->update(['parent_id' => $parentId, 'sort' => $sort]);
            }
        }
    }

    public function down(): void
    {
        $group = DB::table('menu_items')
            ->where('location', 'footer')
            ->where('label', self::GROUP['label'])
            ->first(['id']);

        if (! $group) {
            return;
        }

        DB::table('menu_items')->where('parent_id', $group->id)->update(['parent_id' => null]);
        DB::table('menu_items')->where('id', $group->id)->delete();
    }
};
