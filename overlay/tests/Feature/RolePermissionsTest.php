<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Compounds\Models\Compound;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Leads\Models\Lead;
use Modules\Properties\Models\Property;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * الأدوار والعزل. الاختبارات دي بتحمي حاجتين بيتكسروا بسهولة:
 *   1. راوت اتضاف من غير permission فبقى مفتوح للكل.
 *   2. صف بيتفتح بالـ id مباشرة من غير ما يعدّي على فلتر الملكية.
 */
class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $broker;

    private User $company;

    private User $customer;

    private Property $brokerUnit;

    private Property $companyUnit;

    private Compound $companyProject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->admin = $this->userWithRole('admin');
        $this->broker = $this->userWithRole('broker');
        $this->company = $this->userWithRole('company');
        $this->customer = $this->userWithRole('customer');

        $this->companyProject = Compound::create([
            'name' => 'مشروع الشركة', 'slug' => 'company-project', 'owner_id' => $this->company->id,
        ]);

        $this->brokerUnit = Property::create([
            'title' => 'وحدة الوسيط', 'slug' => 'broker-unit', 'ref' => 'BRK-1', 'owner_id' => $this->broker->id,
        ]);

        $this->companyUnit = Property::create([
            'title' => 'وحدة الشركة', 'slug' => 'company-unit', 'ref' => 'CMP-1',
            'owner_id' => $this->company->id, 'compound_id' => $this->companyProject->id,
        ]);

        // وحدة المنصّة — الأدمن بس هو اللي بيشوفها
        Property::create(['title' => 'وحدة المنصّة', 'slug' => 'platform-unit', 'ref' => 'PLT-1']);
    }

    private function userWithRole(string $role): User
    {
        $user = User::create([
            'name' => $role,
            'email' => "{$role}@test.local",
            'password' => 'password123',
        ]);

        $user->syncRoles([$role]);

        return $user;
    }

    /* ---------------------------------------------------------------- */
    /* من يفتح إيه */
    /* ---------------------------------------------------------------- */

    public static function accessMatrix(): array
    {
        // [الدور, المسار, الكود المتوقّع]
        return [
            'admin → العقارات' => ['admin', '/admin/properties', 200],
            'admin → الكمبوندات' => ['admin', '/admin/compounds', 200],
            'admin → المستخدمون' => ['admin', '/admin/users', 200],
            'admin → الإعدادات' => ['admin', '/admin/settings/general', 200],
            'admin → الطلبات' => ['admin', '/admin/leads', 200],

            'broker → العقارات' => ['broker', '/admin/properties', 200],
            'broker → الطلبات' => ['broker', '/admin/leads', 200],
            'broker ⨯ الكمبوندات' => ['broker', '/admin/compounds', 403],
            'broker ⨯ المستخدمون' => ['broker', '/admin/users', 403],
            'broker ⨯ الإعدادات' => ['broker', '/admin/settings/general', 403],
            'broker ⨯ المطوّرون' => ['broker', '/admin/developers', 403],
            'broker ⨯ المدونة' => ['broker', '/admin/posts', 403],

            'company → العقارات' => ['company', '/admin/properties', 200],
            'company → الكمبوندات' => ['company', '/admin/compounds', 200],
            'company → الطلبات' => ['company', '/admin/leads', 200],
            'company ⨯ المستخدمون' => ['company', '/admin/users', 403],
            'company ⨯ الإعدادات' => ['company', '/admin/settings/general', 403],
        ];
    }

    #[DataProvider('accessMatrix')]
    public function test_role_access_matrix(string $role, string $path, int $expected): void
    {
        $this->actingAs($this->{$role})->get($path)->assertStatus($expected);
    }

    public function test_customer_is_pushed_out_of_the_dashboard(): void
    {
        $this->actingAs($this->customer)
            ->get('/admin')
            ->assertRedirect('/ar/account');
    }

    public function test_guest_going_to_the_dashboard_gets_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_guest_going_to_the_account_gets_the_site_login(): void
    {
        $this->get('/ar/account')->assertRedirect('/ar/login');
    }

    /* ---------------------------------------------------------------- */
    /* العزل */
    /* ---------------------------------------------------------------- */

    public function test_broker_only_sees_his_own_units(): void
    {
        $rows = $this->actingAs($this->broker)->get('/admin/properties')
            ->viewData('page')['props']['rows']['data'];

        $this->assertCount(1, $rows);
        $this->assertSame('وحدة الوسيط', $rows[0]['title']);
    }

    public function test_admin_sees_every_unit(): void
    {
        $rows = $this->actingAs($this->admin)->get('/admin/properties')
            ->viewData('page')['props']['rows']['data'];

        $this->assertCount(3, $rows);
    }

    public function test_broker_cannot_open_another_owners_unit_by_id(): void
    {
        // ده الاختبار المهم: القائمة مبتوريهاش، بس هل الراوت المباشر بيوقفه؟
        $this->actingAs($this->broker)
            ->get("/admin/properties/{$this->companyUnit->id}/edit")
            ->assertNotFound();
    }

    public function test_broker_cannot_update_another_owners_unit(): void
    {
        $this->actingAs($this->broker)
            ->put("/admin/properties/{$this->companyUnit->id}", [
                'title' => 'اختراق', 'purpose' => 'sale', 'is_active' => true,
            ])
            ->assertNotFound();

        $this->assertSame('وحدة الشركة', $this->companyUnit->fresh()->title);
    }

    public function test_broker_cannot_delete_another_owners_unit(): void
    {
        $this->actingAs($this->broker)
            ->delete("/admin/properties/{$this->companyUnit->id}")
            ->assertNotFound();

        $this->assertNotNull($this->companyUnit->fresh());
    }

    public function test_new_unit_is_owned_by_its_creator(): void
    {
        $this->actingAs($this->broker)->post('/admin/properties', [
            'title' => 'وحدة جديدة', 'purpose' => 'sale', 'is_active' => true,
            // محاولة صريحة لتزوير المالك — لازم تتجاهل
            'owner_id' => $this->company->id,
        ])->assertRedirect();

        $created = Property::where('title', 'وحدة جديدة')->firstOrFail();

        $this->assertSame($this->broker->id, $created->owner_id);
    }

    public function test_admin_can_assign_any_owner(): void
    {
        $this->actingAs($this->admin)->post('/admin/properties', [
            'title' => 'وحدة موجّهة', 'purpose' => 'sale', 'is_active' => true,
            'owner_id' => $this->broker->id,
        ])->assertRedirect();

        $this->assertSame($this->broker->id, Property::where('title', 'وحدة موجّهة')->firstOrFail()->owner_id);
    }

    /* ---------------------------------------------------------------- */
    /* الطلبات */
    /* ---------------------------------------------------------------- */

    public function test_a_lead_on_a_unit_goes_to_its_owner(): void
    {
        $this->post('/ar/leads', [
            'name' => 'عميل', 'phone' => '01000000000',
            'property_id' => $this->companyUnit->id, 'source' => 'property',
        ])->assertRedirect();

        $lead = Lead::firstOrFail();

        $this->assertSame($this->company->id, $lead->owner_id);
        $this->assertNull($lead->user_id, 'الزائر مش مسجّل، فمفيش user_id');
    }

    public function test_a_logged_in_customer_sees_his_request(): void
    {
        $this->actingAs($this->customer)->post('/ar/leads', [
            'name' => 'عميل', 'phone' => '01000000000',
            'property_id' => $this->brokerUnit->id, 'source' => 'property',
        ])->assertRedirect();

        $this->assertSame($this->customer->id, Lead::firstOrFail()->user_id);

        $requests = $this->actingAs($this->customer)->get('/ar/account/requests')
            ->viewData('page')['props']['requests'];

        $this->assertCount(1, $requests);
        $this->assertSame('وحدة الوسيط', $requests[0]['subject']);
    }

    public function test_each_owner_only_sees_his_own_leads(): void
    {
        Lead::create(['name' => 'طلب الوسيط', 'phone' => '1', 'owner_id' => $this->broker->id]);
        Lead::create(['name' => 'طلب الشركة', 'phone' => '2', 'owner_id' => $this->company->id]);
        Lead::create(['name' => 'طلب المنصّة', 'phone' => '3']);

        $broker = $this->actingAs($this->broker)->get('/admin/leads')
            ->viewData('page')['props']['rows']['data'];

        $this->assertCount(1, $broker);
        $this->assertSame('طلب الوسيط', $broker[0]['name']);

        $admin = $this->actingAs($this->admin)->get('/admin/leads')
            ->viewData('page')['props']['rows']['data'];

        $this->assertCount(3, $admin);
    }

    /* ---------------------------------------------------------------- */
    /* المفضّلة */
    /* ---------------------------------------------------------------- */

    public function test_customer_can_save_and_unsave_a_property(): void
    {
        $this->actingAs($this->customer)
            ->post("/ar/favorites/{$this->brokerUnit->id}")
            ->assertRedirect();

        $this->assertCount(1, $this->customer->favorites()->get());

        $this->actingAs($this->customer)->post("/ar/favorites/{$this->brokerUnit->id}");

        $this->assertCount(0, $this->customer->fresh()->favorites()->get());
    }

    public function test_guest_cannot_save_a_property(): void
    {
        $this->post("/ar/favorites/{$this->brokerUnit->id}")->assertRedirect('/ar/login');
    }

    /* ---------------------------------------------------------------- */
    /* التسجيل */
    /* ---------------------------------------------------------------- */

    public function test_public_registration_only_creates_customers(): void
    {
        $this->post('/ar/register', [
            'name' => 'زائر', 'email' => 'visitor@test.local', 'phone' => '01000000000',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('/ar/account');

        $user = User::where('email', 'visitor@test.local')->firstOrFail();

        $this->assertTrue($user->hasRole('customer'));
        $this->assertFalse($user->isStaff(), 'العميل مالوش أي صلاحية لوحة');
    }

    public function test_suspended_account_cannot_sign_in(): void
    {
        $this->customer->update(['is_active' => false]);

        $this->post('/ar/login', ['email' => $this->customer->email, 'password' => 'password123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
