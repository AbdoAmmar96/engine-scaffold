<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Core\Models\Activity;
use Modules\Leads\Models\Lead;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;
use Tests\TestCase;

/**
 * التقارير · سجل النشاط · شوهدت مؤخرًا.
 */
class ReportsAndActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::create(['name' => $role, 'email' => "{$role}@test.local", 'password' => 'password123']);
        $user->syncRoles([$role]);

        return $user;
    }

    private function unit(string $ref, array $extra = []): Property
    {
        return Property::create(array_merge([
            'title' => "وحدة {$ref}", 'ref' => $ref, 'purpose' => 'sale', 'type' => 'شقة',
            'status' => 'published', 'is_active' => true,
        ], $extra));
    }

    /* ---------------------------------------------------------------- */
    /* التقارير */
    /* ---------------------------------------------------------------- */

    public function test_reports_are_for_marketing_and_admins_only(): void
    {
        $this->actingAs($this->userWithRole('marketing'))->get('/admin/reports')->assertOk();
        $this->actingAs($this->userWithRole('admin'))->get('/admin/reports')->assertOk();

        $this->actingAs($this->userWithRole('data_entry'))->get('/admin/reports')->assertForbidden();
        $this->actingAs($this->userWithRole('editor'))->get('/admin/reports')->assertForbidden();
        $this->actingAs($this->userWithRole('consultant'))->get('/admin/reports')->assertForbidden();
    }

    public function test_every_number_comes_from_the_tables(): void
    {
        $cairo = Location::create(['name' => 'القاهرة الجديدة']);

        $unit = $this->unit('A', ['location_id' => $cairo->id, 'views_count' => 40]);
        $this->unit('B', ['status' => 'pending']);

        Lead::create(['name' => 'ع', 'phone' => '1', 'source' => 'property', 'status' => 'new', 'property_id' => $unit->id]);
        Lead::create(['name' => 'ب', 'phone' => '2', 'source' => 'contact', 'status' => 'won']);

        $props = $this->actingAs($this->userWithRole('admin'))
            ->get('/admin/reports')
            ->viewData('page')['props'];

        $totals = collect($props['totals'])->pluck('value', 'label');

        $this->assertSame(1, $totals['وحدات منشورة']);
        $this->assertSame(1, $totals['تحت المراجعة']);
        $this->assertSame(40, $totals['مشاهدات الوحدات']);

        $this->assertEqualsCanonicalizing(['فورم اتصل بنا' => 1, 'صفحة وحدة' => 1], collect($props['leadsBySource'])->pluck('value', 'label')->all());
        $this->assertSame(1, collect($props['leadsByStatus'])->firstWhere('label', 'جديد')['value']);
        $this->assertSame('القاهرة الجديدة', $props['topAreas'][0]['label']);
        $this->assertSame(40, $props['topProperties'][0]['views']);
    }

    public function test_the_daily_line_fills_empty_days_with_zero(): void
    {
        $props = $this->actingAs($this->userWithRole('admin'))->get('/admin/reports')->viewData('page')['props'];

        // الخط لازم يبقى فيه نقطة لكل يوم، وإلا الشكل بيكدب
        $this->assertCount($props['days'], $props['daily']);
        $this->assertSame(0, collect($props['daily'])->sum('value'));
    }

    /* ---------------------------------------------------------------- */
    /* سجل النشاط */
    /* ---------------------------------------------------------------- */

    public function test_dashboard_actions_land_in_the_log(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post('/admin/locations', ['name' => 'منطقة جديدة', 'is_active' => true]);

        $row = Activity::sole();

        $this->assertSame('created', $row->action);
        $this->assertSame($admin->id, $row->user_id);
        $this->assertSame('منطقة جديدة', $row->subject_label);
        $this->assertSame('منطقة', $row->subjectLabel());
    }

    public function test_an_edit_records_which_fields_moved_but_not_their_values(): void
    {
        $admin = $this->userWithRole('admin');
        $area = Location::create(['name' => 'قديمة']);

        Activity::query()->delete();

        $this->actingAs($admin)->put("/admin/locations/{$area->id}", ['name' => 'جديدة', 'is_active' => true]);

        $row = Activity::sole();

        $this->assertSame('updated', $row->action);
        $this->assertContains('name', $row->changed);
        // القيم نفسها مش بتتخزّن — فيها بيانات عملاء وهاشات
        $this->assertStringNotContainsString('قديمة', json_encode($row->changed, JSON_UNESCAPED_UNICODE));
    }

    public function test_seeders_and_commands_do_not_fill_the_log(): void
    {
        $this->unit('SEEDED');
        $this->artisan('seo:landing-pages');

        // مفيش مستخدم مسجّل = مش فعل إنسان = مش بيتسجّل
        $this->assertSame(0, Activity::count());
    }

    public function test_view_counters_do_not_fill_the_log(): void
    {
        $unit = $this->unit('VIEWS');
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->get("/ar/properties/{$unit->slug}");

        $this->assertSame(0, Activity::where('action', 'updated')->count());
    }

    public function test_the_log_is_read_only_and_admin_only(): void
    {
        $this->actingAs($this->userWithRole('admin'))->get('/admin/activity')->assertOk();
        $this->actingAs($this->userWithRole('super_admin'))->get('/admin/activity')->assertOk();

        $this->actingAs($this->userWithRole('marketing'))->get('/admin/activity')->assertForbidden();
        $this->actingAs($this->userWithRole('data_entry'))->get('/admin/activity')->assertForbidden();

        // مفيش راوت تعديل ولا حذف أصلًا
        $this->assertNull(app('router')->getRoutes()->getByName('admin.activity.destroy'));
    }

    public function test_the_log_can_be_filtered(): void
    {
        $admin = $this->userWithRole('admin');
        $this->actingAs($admin);

        $this->post('/admin/locations', ['name' => 'منطقة', 'is_active' => true]);
        $this->post('/admin/developers', ['name' => 'مطوّر', 'is_active' => true]);

        $rows = $this->get('/admin/activity?subject=Location')->viewData('page')['props']['rows']['data'];

        $this->assertCount(1, $rows);
        $this->assertSame('منطقة', $rows[0]['label']);
    }

    /* ---------------------------------------------------------------- */
    /* شوهدت مؤخرًا */
    /* ---------------------------------------------------------------- */

    public function test_a_signed_in_visit_is_remembered_across_devices(): void
    {
        $customer = $this->userWithRole('customer');
        $unit = $this->unit('SEEN');

        $this->actingAs($customer)->get("/ar/properties/{$unit->slug}")->assertOk();

        $seen = $this->get('/ar')->viewData('page')['props']['recentlyViewed'];

        $this->assertSame(['SEEN'], array_column($seen, 'ref'));
    }

    public function test_a_second_visit_does_not_duplicate_the_row(): void
    {
        $customer = $this->userWithRole('customer');
        $unit = $this->unit('SEEN');

        $this->actingAs($customer);
        $this->get("/ar/properties/{$unit->slug}");
        $this->get("/ar/properties/{$unit->slug}");

        $this->assertSame(1, DB::table('recently_viewed')->count());
    }

    public function test_a_guest_gets_nothing_from_the_server(): void
    {
        $unit = $this->unit('SEEN');
        $this->get("/ar/properties/{$unit->slug}");

        // الزائر قايمته في المتصفح — السيرفر مبيعرفش عنه حاجة
        $this->assertSame([], $this->get('/ar')->viewData('page')['props']['recentlyViewed']);
    }

    public function test_the_browser_endpoint_keeps_the_order_it_was_given(): void
    {
        $a = $this->unit('A');
        $b = $this->unit('B');
        $hidden = $this->unit('HIDDEN', ['status' => 'pending']);

        $rows = $this->getJson("/ar/recently-viewed?ids={$b->id},{$a->id},{$hidden->id}")
            ->assertOk()
            ->json('properties');

        // الترتيب من المتصفح (الأحدث زيارة الأول)، والمخفية مش بتطلع
        $this->assertSame(['B', 'A'], array_column($rows, 'ref'));
    }

    public function test_the_endpoint_ignores_rubbish(): void
    {
        $this->getJson('/ar/recently-viewed?ids=abc,,-5')->assertOk()->assertJson(['properties' => []]);
        $this->getJson('/ar/recently-viewed')->assertOk()->assertJson(['properties' => []]);
    }
}
