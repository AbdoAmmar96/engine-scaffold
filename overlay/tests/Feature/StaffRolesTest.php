<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\AdminUserSeeder;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Leads\Models\Lead;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;
use Tests\TestCase;

/**
 * الأدوار الجديدة: مدخل بيانات · تسويق · مستشار · معلن · سوبر أدمن.
 *
 * الفكرة كلها إن الصلاحية هي اللي بتحكم مش اسم الدور، فالاختبارات هنا
 * بتقيس السلوك: مين بينشر، مين بيمسح، ومين بيتحوّل من فوق اللوحة لبره.
 */
class StaffRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
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

    private function unit(array $extra = []): Property
    {
        return Property::create(array_merge([
            'title' => 'وحدة', 'purpose' => 'sale', 'type' => 'شقة', 'status' => 'published',
        ], $extra));
    }

    /** الحقول اللي الفورم بيعرضها للمستخدم الحالي */
    private function formFields(string $path): array
    {
        return array_column(
            $this->get($path)->viewData('page')['props']['resource']['fields'],
            'name',
        );
    }

    /* ---------------------------------------------------------------- */
    /* مدخل البيانات: بيدخل ويعدّل، مش بينشر ولا بيمسح */
    /* ---------------------------------------------------------------- */

    public function test_data_entry_reaches_the_catalog(): void
    {
        $this->actingAs($this->userWithRole('data_entry'));

        $this->get('/admin/properties')->assertOk();
        $this->get('/admin/compounds')->assertOk();
        $this->get('/admin/media')->assertOk();
    }

    public function test_data_entry_has_no_status_field(): void
    {
        $this->actingAs($this->userWithRole('data_entry'));

        $fields = $this->formFields('/admin/properties/create');

        $this->assertNotContains('status', $fields);
        $this->assertNotContains('is_featured', $fields);
        // بس بيشوف كل الوحدات، مش بتاعته بس
        $this->assertContains('owner_id', $fields);
    }

    public function test_a_unit_created_by_data_entry_waits_for_review(): void
    {
        $this->actingAs($this->userWithRole('data_entry'));

        // بيبعت status=published صراحةً — لازم يتجاهل
        $this->post('/admin/properties', [
            'title' => 'وحدة مدخل البيانات', 'purpose' => 'sale', 'type' => 'شقة',
            'status' => 'published', 'is_featured' => true, 'is_active' => true,
        ])->assertRedirect();

        $property = Property::sole();

        $this->assertSame('pending', $property->status);
        $this->assertFalse($property->is_featured);
    }

    public function test_data_entry_editing_a_live_unit_does_not_unpublish_it(): void
    {
        $unit = $this->unit(['title' => 'وحدة منشورة']);

        $this->actingAs($this->userWithRole('data_entry'))
            ->put("/admin/properties/{$unit->id}", [
                'title' => 'العنوان اتظبط', 'purpose' => 'sale', 'type' => 'شقة', 'is_active' => true,
            ])->assertRedirect();

        $unit->refresh();

        // التعديل بيعدّي، الحالة مبتتلمسش — لا بتُنشر ولا بترجع للمراجعة
        $this->assertSame('العنوان اتظبط', $unit->title);
        $this->assertSame('published', $unit->status);
    }

    public function test_data_entry_cannot_delete(): void
    {
        $unit = $this->unit();
        $area = Location::create(['name' => 'منطقة']);

        $this->actingAs($this->userWithRole('data_entry'));

        $this->delete("/admin/properties/{$unit->id}")->assertRedirect();
        $this->delete("/admin/locations/{$area->id}")->assertRedirect();

        $this->assertModelExists($unit);
        $this->assertModelExists($area);
    }

    /* ---------------------------------------------------------------- */
    /* التسويق: بيميّز، مش بينشر */
    /* ---------------------------------------------------------------- */

    public function test_marketing_features_but_does_not_publish(): void
    {
        $unit = $this->unit(['status' => 'pending']);

        $this->actingAs($this->userWithRole('marketing'));

        $fields = $this->formFields("/admin/properties/{$unit->id}/edit");

        $this->assertContains('is_featured', $fields);
        $this->assertNotContains('status', $fields);

        $this->put("/admin/properties/{$unit->id}", [
            'title' => 'وحدة', 'purpose' => 'sale', 'type' => 'شقة', 'is_active' => true,
            'is_featured' => true, 'status' => 'published',
        ])->assertRedirect();

        $unit->refresh();

        $this->assertTrue($unit->is_featured);
        $this->assertSame('pending', $unit->status);
    }

    public function test_marketing_edits_landing_page_copy(): void
    {
        $this->actingAs($this->userWithRole('marketing'))
            ->get('/admin/landing-pages')->assertOk();
    }

    /* ---------------------------------------------------------------- */
    /* المستشار: طلباته هو بس */
    /* ---------------------------------------------------------------- */

    public function test_consultant_only_sees_assigned_leads(): void
    {
        $consultant = $this->userWithRole('consultant');

        Lead::create(['name' => 'طلب مسنود', 'phone' => '1', 'owner_id' => $consultant->id, 'source' => 'contact', 'status' => 'new']);
        Lead::create(['name' => 'طلب غيره', 'phone' => '2', 'source' => 'contact', 'status' => 'new']);

        $rows = $this->actingAs($consultant)->get('/admin/leads')->viewData('page')['props']['rows']['data'];

        $this->assertSame(['طلب مسنود'], array_column($rows, 'name'));
    }

    public function test_consultant_cannot_reach_the_catalog(): void
    {
        $this->actingAs($this->userWithRole('consultant'));

        $this->get('/admin/properties')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    /* ---------------------------------------------------------------- */
    /* المعلن: مش موظف */
    /* ---------------------------------------------------------------- */

    public function test_a_lister_owns_units_without_getting_the_dashboard(): void
    {
        $lister = $this->userWithRole('lister');

        $this->assertTrue($lister->can('manage listings'));
        $this->assertFalse($lister->isStaff());

        $this->actingAs($lister)->get('/admin')->assertRedirect('/ar/account');
    }

    public function test_account_emails_are_hidden_from_everyone_but_user_managers(): void
    {
        $broker = $this->userWithRole('broker');

        $owners = function (): array {
            $field = collect($this->get('/admin/properties/create')->viewData('page')['props']['resource']['fields'])
                ->firstWhere('name', 'owner_id');

            return array_column($field['options'] ?? [], 'label');
        };

        // مدخل البيانات والتسويق بيشوفوا القايمة عشان يوزّعوا الوحدات،
        // بس من غير إيميلات
        foreach (['data_entry', 'marketing'] as $role) {
            $this->actingAs($this->userWithRole($role));

            $labels = $owners();

            $this->assertNotEmpty($labels);
            $this->assertStringNotContainsString($broker->email, implode(' ', $labels));
            $this->assertContains($broker->name.' #'.$broker->id, $labels);
        }

        // اللي بيدير المستخدمين أصلًا بيشوف الإيميلات في شاشته
        $this->actingAs($this->userWithRole('admin'));
        $this->assertStringContainsString($broker->email, implode(' ', $owners()));
    }

    /* ---------------------------------------------------------------- */
    /* الأدوار: سوبر أدمن بس */
    /* ---------------------------------------------------------------- */

    public function test_admin_cannot_hand_out_staff_roles(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        $roles = array_column(
            collect($this->get('/admin/users/create')->viewData('page')['props']['resource']['fields'])
                ->firstWhere('name', 'role')['options'],
            'value',
        );

        $this->assertSame(['lister', 'customer'], $roles);

        $this->post('/admin/users', [
            'name' => 'وسيط جديد', 'email' => 'new@test.local', 'role' => 'broker',
            'password' => 'password123', 'password_confirmation' => 'password123', 'is_active' => true,
        ])->assertSessionHasErrors('role');
    }

    public function test_admin_cannot_see_or_open_staff_accounts(): void
    {
        $broker = $this->userWithRole('broker');
        $customer = $this->userWithRole('customer');

        $this->actingAs($this->userWithRole('admin'));

        $names = array_column($this->get('/admin/users')->viewData('page')['props']['rows']['data'], 'email');

        $this->assertContains($customer->email, $names);
        $this->assertNotContains($broker->email, $names);

        // ولا بالـ id في العنوان
        $this->get("/admin/users/{$broker->id}/edit")->assertNotFound();
    }

    public function test_super_admin_hands_out_every_role(): void
    {
        $this->actingAs($this->userWithRole('super_admin'));

        $roles = array_column(
            collect($this->get('/admin/users/create')->viewData('page')['props']['resource']['fields'])
                ->firstWhere('name', 'role')['options'],
            'value',
        );

        $this->assertSame(array_keys(RolePermissionSeeder::ROLES), $roles);

        $this->post('/admin/users', [
            'name' => 'وسيط جديد', 'email' => 'new@test.local', 'role' => 'broker',
            'password' => 'password123', 'password_confirmation' => 'password123', 'is_active' => true,
        ])->assertRedirect();

        $this->assertTrue(User::where('email', 'new@test.local')->sole()->hasRole('broker'));
    }

    public function test_the_last_role_manager_cannot_be_demoted_or_deleted(): void
    {
        $boss = $this->userWithRole('super_admin');

        $this->actingAs($boss);

        $this->put("/admin/users/{$boss->id}", [
            'name' => 'boss', 'email' => $boss->email, 'role' => 'editor', 'is_active' => true,
        ])->assertSessionHasErrors('role');

        $this->delete("/admin/users/{$boss->id}")->assertRedirect();
        $this->assertModelExists($boss);
    }

    public function test_reseeding_does_not_undo_the_promotion(): void
    {
        $admin = $this->userWithRole('admin');

        $this->seed(RolePermissionSeeder::class);
        $this->assertTrue($admin->fresh()->hasRole('super_admin'));

        // AdminUserSeeder بيتشغّل بعد RolePermissionSeeder في كل ديبلوي —
        // وكان بيرجّع الدور لـ admin ويلغي الترقية
        $this->seed(AdminUserSeeder::class);

        $this->assertTrue($admin->fresh()->hasRole('super_admin'));
    }

    public function test_a_fresh_install_gets_a_super_admin(): void
    {
        config(['app.admin_email' => null]);

        $this->seed(AdminUserSeeder::class);

        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first();

        $this->assertNotNull($admin, 'أول حساب على تثبيت جديد لازم يقدر يوزّع الأدوار');
    }

    public function test_the_seeder_promotes_the_first_admin_so_nobody_is_locked_out(): void
    {
        $first = $this->userWithRole('admin');
        $second = User::create(['name' => 'b', 'email' => 'b@test.local', 'password' => 'password123']);
        $second->syncRoles(['admin']);

        // التركيب ده هو اللي بيحصل على أي تثبيت قديم أول ما الدور يتضاف
        $this->seed(RolePermissionSeeder::class);

        $this->assertTrue($first->fresh()->hasRole('super_admin'));
        $this->assertTrue($second->fresh()->hasRole('admin'));

        // ومبيرقّيش حد تاني في التشغيلة اللي بعدها
        $this->seed(RolePermissionSeeder::class);
        $this->assertTrue($second->fresh()->hasRole('admin'));
    }
}
