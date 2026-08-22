<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Properties\Models\Property;
use Tests\TestCase;

/**
 * الفلاتر والترتيب ودورة الاعتماد.
 * الفلتر اللي بيرجّع كل حاجة أخطر من الفلتر اللي بيرجّع لا حاجة —
 * فكل اختبار هنا بيتأكد إن اللي اتشال فعلًا اتشال.
 */
class ListingFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->unit('CHEAP', ['price_amount' => 1_000_000, 'size' => 90, 'beds' => 2, 'baths' => 1, 'finishing' => 'semi']);
        $this->unit('MID', [
            'price_amount' => 5_000_000, 'size' => 200, 'beds' => 3, 'baths' => 2, 'finishing' => 'full',
            'down_payment' => 500_000, 'monthly_installment' => 20_000, 'installment_years' => 8,
            'delivery_year' => 2027, 'has_garden' => true,
        ]);
        $this->unit('LUXE', [
            'price_amount' => 20_000_000, 'size' => 500, 'beds' => 5, 'baths' => 5, 'finishing' => 'furnished',
            'is_featured' => true, 'has_roof' => true, 'type' => 'فيلا',
        ]);
        $this->unit('SHOP', ['price_amount' => 3_000_000, 'size' => 60, 'type' => 'محل تجاري']);
    }

    private function unit(string $ref, array $extra = []): Property
    {
        return Property::create(array_merge([
            'title' => "وحدة {$ref}",
            'slug' => strtolower($ref),
            'ref' => $ref,
            'purpose' => 'sale',
            'type' => 'شقة',
            'status' => 'published',
            'is_active' => true,
        ], $extra));
    }

    /** @return string[] أكواد النتايج */
    private function refs(array $query): array
    {
        $filters = Catalog::filters(Request::create('/', 'GET', $query));

        return array_column(Catalog::properties('ar', null, $filters), 'ref');
    }

    public function test_no_filter_returns_everything_published(): void
    {
        $this->assertCount(4, $this->refs([]));
    }

    public function test_price_range(): void
    {
        $this->assertSame(['MID', 'LUXE'], $this->sorted($this->refs(['price_min' => 5_000_000])));
        $this->assertSame(['CHEAP', 'SHOP'], $this->sorted($this->refs(['price_max' => 3_000_000])));
        $this->assertSame(['MID'], $this->refs(['price_min' => 4_000_000, 'price_max' => 6_000_000]));
    }

    public function test_area_range(): void
    {
        $this->assertSame(['MID', 'LUXE'], $this->sorted($this->refs(['area_min' => 200])));
        $this->assertSame(['CHEAP', 'SHOP'], $this->sorted($this->refs(['area_max' => 90])));
    }

    public function test_beds_and_baths_are_at_least(): void
    {
        $this->assertSame(['MID', 'LUXE'], $this->sorted($this->refs(['beds' => 3])));
        $this->assertSame(['LUXE'], $this->refs(['baths' => 5]));
    }

    public function test_finishing_and_flags(): void
    {
        $this->assertSame(['LUXE'], $this->refs(['finishing' => 'furnished']));
        $this->assertSame(['LUXE'], $this->refs(['featured' => '1']));
        $this->assertSame(['MID'], $this->refs(['garden' => '1']));
        $this->assertSame(['LUXE'], $this->refs(['roof' => '1']));
    }

    public function test_payment_filters_skip_units_without_a_plan(): void
    {
        // الوحدة اللي مالهاش خطة سداد مبتظهرش في فلتر خطة سداد
        $this->assertSame(['MID'], $this->refs(['down_max' => 600_000]));
        $this->assertSame(['MID'], $this->refs(['monthly_max' => 25_000]));
        $this->assertSame(['MID'], $this->refs(['years_max' => 8]));
        $this->assertSame(['MID'], $this->refs(['delivery' => 2027]));
    }

    public function test_category_splits_commercial_from_residential(): void
    {
        $this->assertSame(['SHOP'], $this->refs(['category' => 'commercial']));
        $this->assertSame(['CHEAP', 'MID', 'LUXE'], $this->sorted($this->refs(['category' => 'residential'])));
    }

    public function test_sorting(): void
    {
        $this->assertSame(['CHEAP', 'SHOP', 'MID', 'LUXE'], $this->refs(['sort' => 'price_asc']));
        $this->assertSame(['LUXE', 'MID', 'SHOP', 'CHEAP'], $this->refs(['sort' => 'price_desc']));
        $this->assertSame(['LUXE', 'MID', 'CHEAP', 'SHOP'], $this->refs(['sort' => 'area_desc']));

        // الترتيب الصريح بيغلب تصدير المميّز
        $this->assertSame('CHEAP', $this->refs(['sort' => 'price_asc'])[0]);
        // والافتراضي بيصدّر المميّز
        $this->assertSame('LUXE', $this->refs([])[0]);
    }

    public function test_unknown_filter_keys_are_ignored(): void
    {
        $filters = Catalog::filters(Request::create('/', 'GET', [
            'sort' => 'drop table', 'purpose' => 'anything', 'finishing' => 'nope', 'price_min' => '-5',
            'evil' => '1',
        ]));

        $this->assertSame('', $filters['sort']);
        $this->assertSame('', $filters['purpose']);
        $this->assertSame('', $filters['finishing']);
        $this->assertSame('', $filters['price_min']);
        $this->assertArrayNotHasKey('evil', $filters);
    }

    /* ---------------------------------------------------------------- */
    /* دورة الاعتماد */
    /* ---------------------------------------------------------------- */

    public function test_unpublished_units_are_invisible_to_visitors(): void
    {
        $this->unit('HIDDEN', ['status' => 'pending']);
        $this->unit('OFF', ['is_active' => false]);

        $refs = $this->refs([]);

        $this->assertNotContains('HIDDEN', $refs);
        $this->assertNotContains('OFF', $refs);
    }

    public function test_a_broker_submission_goes_to_review_not_live(): void
    {
        $broker = User::create(['name' => 'b', 'email' => 'b@t.local', 'password' => 'password123']);
        $broker->syncRoles(['broker']);

        $this->actingAs($broker)->post('/admin/properties', [
            'title' => 'وحدة الوسيط', 'purpose' => 'sale', 'is_active' => true,
            // محاولة نشر مباشر — لازم تتجاهل
            'status' => 'published',
        ])->assertRedirect();

        $created = Property::where('title', 'وحدة الوسيط')->firstOrFail();

        $this->assertSame('pending', $created->status);
        $this->assertNotContains($created->ref, $this->refs([]));
    }

    public function test_editing_a_published_unit_sends_it_back_to_review(): void
    {
        $broker = User::create(['name' => 'b2', 'email' => 'b2@t.local', 'password' => 'password123']);
        $broker->syncRoles(['broker']);

        $unit = $this->unit('OWNED', ['owner_id' => $broker->id]);

        $this->actingAs($broker)->put("/admin/properties/{$unit->id}", [
            'title' => 'عنوان جديد', 'purpose' => 'sale', 'is_active' => true,
        ])->assertRedirect();

        $this->assertSame('pending', $unit->fresh()->status);
    }

    public function test_publishing_generates_a_reference_code_and_date(): void
    {
        $unit = $this->unit('TEMP', ['status' => 'draft']);
        $unit->update(['ref' => null, 'status' => 'draft']);

        $this->assertNull($unit->fresh()->ref);

        $unit->update(['status' => 'published']);

        $fresh = $unit->fresh();

        $this->assertStringStartsWith(Property::REF_PREFIX.'-', (string) $fresh->ref);
        $this->assertNotNull($fresh->published_at);
    }

    /** @param string[] $refs */
    private function sorted(array $refs): array
    {
        $order = ['CHEAP', 'MID', 'LUXE', 'SHOP'];

        usort($refs, fn ($a, $b) => array_search($a, $order, true) <=> array_search($b, $order, true));

        return $refs;
    }
}
