<?php

namespace Tests\Feature;

use App\Support\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Modules\Compounds\Models\Compound;
use Modules\Developers\Models\Developer;
use Tests\TestCase;

/**
 * قسم المطوّرين في صفحة «من نحن».
 *
 * القاعدة اللي بيقوم عليها القسم: الكارت لازم يوصّل لمشاريع يقدر الزائر يشوفها.
 * فالمطوّر من غير مشاريع منشورة مابيظهرش، والعدّاد بيعدّ المنشور بس.
 */
class DevelopersSectionTest extends TestCase
{
    use RefreshDatabase;

    private function makeDeveloper(string $name, string $nameEn = 'Dev'): Developer
    {
        return Developer::create(['name' => $name, 'name_en' => $nameEn, 'is_active' => true]);
    }

    private function makeCompound(Developer $developer, bool $isActive = true): Compound
    {
        return Compound::create([
            'name' => 'مشروع '.uniqid(),
            'developer_id' => $developer->id,
            'is_active' => $isActive,
        ]);
    }

    public function test_a_developer_with_published_projects_is_listed_with_its_count(): void
    {
        $developer = $this->makeDeveloper('بناة المستقبل');
        $this->makeCompound($developer);
        $this->makeCompound($developer);

        $cards = Catalog::developers('ar');

        $this->assertCount(1, $cards);
        $this->assertSame('بناة المستقبل', $cards[0]['name']);
        $this->assertSame(2, $cards[0]['compounds']);
    }

    public function test_a_developer_with_no_projects_is_hidden(): void
    {
        $this->makeDeveloper('مطوّر من غير مشاريع');

        $this->assertSame([], Catalog::developers('ar'));
    }

    public function test_unpublished_projects_neither_list_nor_count(): void
    {
        $developer = $this->makeDeveloper('مطوّر بمشروع متوقّف');
        $this->makeCompound($developer, isActive: false);

        $this->assertSame([], Catalog::developers('ar'));

        // مشروع منشور واحد بيرجّعه للقايمة — والعدّاد بيفضل 1 مش 2
        $this->makeCompound($developer);
        $cards = Catalog::developers('ar');

        $this->assertCount(1, $cards);
        $this->assertSame(1, $cards[0]['compounds']);
    }

    public function test_an_inactive_developer_is_hidden(): void
    {
        $developer = $this->makeDeveloper('مطوّر متوقّف');
        $developer->update(['is_active' => false]);
        $this->makeCompound($developer);

        $this->assertSame([], Catalog::developers('ar'));
    }

    public function test_the_card_links_to_the_developer_projects(): void
    {
        $developer = $this->makeDeveloper('الوادي القابضة');
        $compound = $this->makeCompound($developer);

        $url = Catalog::developers('ar')[0]['url'];

        // الكارت بقى بيوصّل لصفحة المطوّر نفسها بدل نتيجة بحث في الكمبوندات
        $this->assertSame("/ar/developers/{$developer->fresh()->slug}", $url);

        // والصفحة دي لازم ترجّع مشاريعه فعلًا — مش تفتح فاضية
        $found = Catalog::developer('ar', $developer->fresh()->slug)['compounds'];

        $this->assertCount(1, $found);
        $this->assertSame($compound->name, $found[0]['name']);
    }

    public function test_the_about_page_carries_the_developers(): void
    {
        $developer = $this->makeDeveloper('شركة المروج للتطوير');
        $this->makeCompound($developer);

        // البروب بيتخزّن JSON-escaped في data-page، فالتأكيد على الـ prop نفسه مش على الـ HTML
        $this->get('/ar/about')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Site/About')
                ->has('developers', 1)
                ->where('developers.0.name', 'شركة المروج للتطوير')
                ->where('developers.0.compounds', 1));
    }
}
