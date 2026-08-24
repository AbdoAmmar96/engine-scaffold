<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Core\Database\Seeders\MenuSeeder;
use Modules\Core\Models\MenuItem;
use Modules\Core\Services\SettingsService;
use Tests\TestCase;

/**
 * تقسيم الفوتر لأعمدة.
 *
 * الفوتر كان مسطّح: لينك واحد ورا التاني تحت عنوان «الموقع». دلوقتي
 * العنصر اللي له أبناء بيبقى عمود بعنوانه — نفس بيانات الهيدر، فالأدمن
 * بيرتّبه من `/admin/menus` من غير كود.
 *
 * أخطر جزء هو **النقل على تثبيت قايم**: `put` بيتعرّف بـ (location + label)
 * فبيلاقي اللينك المسطّح ومبيحركوش، والمجموعة كانت هتتعمل فاضية. النقل بقى
 * في ميجريشن — لمرة واحدة — لأن السيدر بيشتغل كل رفعة وكان هيرجّع اللينك
 * للمجموعة كل مرة رغم إن الأدمن طلّعه عن قصد.
 */
class FooterMenuTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, list<string>> العمود ← لينكاته */
    private function columns(): array
    {
        $footer = $this->get('/ar')->assertOk()->viewData('page')['props']['menu']['footer'];

        $columns = [];

        foreach ($footer as $item) {
            if ($item['children'] !== []) {
                $columns[$item['label']] = array_column($item['children'], 'label');
            }
        }

        return $columns;
    }

    public function test_the_footer_groups_the_services_into_their_own_column(): void
    {
        $this->seed(MenuSeeder::class);

        $this->assertSame(
            ['العقارات', 'عقارات تجارية', 'الكمبوندات', 'المطوّرون'],
            $this->columns()['خدماتنا'] ?? [],
        );
    }

    public function test_the_rest_stays_flat_outside_the_group(): void
    {
        $this->seed(MenuSeeder::class);

        $footer = $this->get('/ar')->assertOk()->viewData('page')['props']['menu']['footer'];
        $flat = array_column(array_filter($footer, fn ($i) => $i['children'] === []), 'label');

        $this->assertContains('من نحن', $flat);
        $this->assertContains('اتصل بنا', $flat);
        // ومفيش تكرار: اللي دخل المجموعة مايظهرش تاني لوحده
        $this->assertNotContains('العقارات', $flat);
    }

    /** الشكل القديم: فوتر مسطّح من غير أي مجموعة */
    private function oldFooter(array $links): void
    {
        MenuItem::query()->where('location', 'footer')->delete();

        foreach ($links as $i => [$label, $url]) {
            MenuItem::create([
                'location' => 'footer', 'label' => $label, 'url' => $url,
                'sort' => $i, 'is_active' => true,
            ]);
        }

        MenuItem::flush();
    }

    /** يشغّل الميجريشن لوحده — RefreshDatabase شغّله قبل ما البيانات تتزرع */
    private function migrate(): void
    {
        require_once base_path('Modules/Core/database/migrations/2026_08_28_000001_group_footer_services_menu.php');

        (require base_path('Modules/Core/database/migrations/2026_08_28_000001_group_footer_services_menu.php'))->up();

        MenuItem::flush();
    }

    public function test_an_older_install_gets_its_flat_links_moved_into_the_group(): void
    {
        $this->oldFooter([
            ['العقارات', '/properties'],
            ['عقارات تجارية', '/properties/commercial'],
            ['الكمبوندات', '/compounds'],
            ['المطوّرون', '/developers'],
            ['من نحن', '/about'],
        ]);

        $this->migrate();

        $this->assertSame(
            ['العقارات', 'عقارات تجارية', 'الكمبوندات', 'المطوّرون'],
            $this->columns()['خدماتنا'] ?? [],
        );

        // نقل مش تكرار — الصفوف القديمة هي نفسها اللي اتحركت
        $this->assertSame(1, MenuItem::where('location', 'footer')->where('label', 'العقارات')->count());
    }

    public function test_the_move_happens_once_so_the_admin_can_undo_it(): void
    {
        $this->seed(MenuSeeder::class);

        // الأدمن طلّع «الكمبوندات» بره المجموعة
        $compounds = MenuItem::where('location', 'footer')->where('label', 'الكمبوندات')->sole();
        $compounds->update(['parent_id' => null]);
        MenuItem::flush();

        // رفعة تانية: migrate ثم seed زي deploy.sh بالظبط
        $this->migrate();
        $this->seed(MenuSeeder::class);

        $this->assertNull($compounds->fresh()->parent_id, 'رجع للمجموعة رغم إن الأدمن طلّعه');
    }

    public function test_a_link_the_admin_repointed_is_left_alone(): void
    {
        // نفس الاسم لكن وجهة مختلفة — يبقى مش اللينك الافتراضي
        $this->oldFooter([
            ['العقارات', '/properties'],
            ['المطوّرون', 'https://example.com/devs'],
        ]);

        $this->migrate();

        $this->assertNull(
            MenuItem::where('location', 'footer')->where('label', 'المطوّرون')->sole()->parent_id,
        );
    }

    public function test_the_migration_does_nothing_on_a_fresh_install(): void
    {
        $this->migrate();

        $this->assertSame(0, DB::table('menu_items')->where('location', 'footer')->count());
    }

    public function test_a_closed_section_drops_out_of_its_footer_group(): void
    {
        $this->seed(MenuSeeder::class);
        app(SettingsService::class)->setMany('general', ['blog_enabled' => '1']);

        $footer = $this->get('/ar')->assertOk()->viewData('page')['props']['menu']['footer'];
        $this->assertContains('المدونة', array_column($footer, 'label'));

        app(SettingsService::class)->setMany('general', ['blog_enabled' => '0']);

        $footer = $this->get('/ar')->assertOk()->viewData('page')['props']['menu']['footer'];
        $this->assertNotContains('المدونة', array_column($footer, 'label'));
    }
}
