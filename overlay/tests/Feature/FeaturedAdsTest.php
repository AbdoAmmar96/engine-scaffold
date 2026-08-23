<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Compounds\Models\Compound;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Marketing\Models\FeaturedAd;
use Modules\Properties\Models\Property;
use Tests\TestCase;

/**
 * المساحات الإعلانية المجدولة.
 *
 * الخطر الأساسي إن إعلان يفضل معروض بعد ما ينتهي — مساحة اتدفع فيها
 * فلوس لمدة محدّدة. فمعظم الاختبارات هنا بتقيس اللي **مش** بيتعرض.
 */
class FeaturedAdsTest extends TestCase
{
    use RefreshDatabase;

    private Property $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->unit = Property::create([
            'title' => 'وحدة الإعلان', 'ref' => 'AD-1', 'purpose' => 'sale', 'type' => 'شقة',
            'status' => 'published', 'is_active' => true,
        ]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::create(['name' => $role, 'email' => "{$role}@test.local", 'password' => 'password123']);
        $user->syncRoles([$role]);

        return $user;
    }

    private function ad(array $extra = []): FeaturedAd
    {
        return FeaturedAd::create(array_merge([
            'position' => 'listing',
            'property_id' => $this->unit->id,
            'status' => 'approved',
            'is_active' => true,
        ], $extra));
    }

    /** الإعلانات اللي الصفحة بتعرضها فعلًا */
    private function shown(string $path = '/ar/properties'): array
    {
        return array_column($this->get($path)->viewData('page')['props']['ads'] ?? [], 'adId');
    }

    /* ---------------------------------------------------------------- */
    /* الجدولة */
    /* ---------------------------------------------------------------- */

    public function test_an_open_ended_ad_shows(): void
    {
        $ad = $this->ad();

        $this->assertSame([$ad->id], $this->shown());
    }

    public function test_an_ad_inside_its_window_shows(): void
    {
        $ad = $this->ad(['starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);

        $this->assertSame([$ad->id], $this->shown());
    }

    public function test_a_scheduled_ad_stays_hidden_until_its_day(): void
    {
        $this->ad(['starts_at' => now()->addWeek()]);

        $this->assertSame([], $this->shown());
    }

    public function test_an_expired_ad_stops_on_its_own(): void
    {
        $this->ad(['starts_at' => now()->subMonth(), 'ends_at' => now()->subDay()]);

        // ده بيت القصيد: محدش محتاج يفتكر يقفلها
        $this->assertSame([], $this->shown());
    }

    public function test_a_pending_or_rejected_ad_never_shows(): void
    {
        $this->ad(['status' => 'pending']);
        $this->ad(['status' => 'rejected']);

        $this->assertSame([], $this->shown());
    }

    public function test_a_paused_ad_does_not_show(): void
    {
        $this->ad(['is_active' => false]);

        $this->assertSame([], $this->shown());
    }

    public function test_an_ad_on_a_unit_that_left_the_site_does_not_show(): void
    {
        $ad = $this->ad();

        $this->unit->update(['status' => 'sold']);
        $this->assertSame([], $this->shown());

        $this->unit->update(['status' => 'published', 'is_active' => false]);
        $this->assertSame([], $this->shown());

        $this->unit->update(['is_active' => true]);
        $this->assertSame([$ad->id], $this->shown());
    }

    public function test_each_position_only_shows_its_own_ads(): void
    {
        $listing = $this->ad(['position' => 'listing']);
        $hero = $this->ad(['position' => 'hero']);

        $this->assertSame([$listing->id], $this->shown('/ar/properties'));
        $this->assertSame([$hero->id], $this->shown('/ar'));
    }

    public function test_higher_priority_comes_first(): void
    {
        $low = $this->ad(['priority' => 1]);
        $high = $this->ad(['priority' => 9]);

        $this->assertSame([$high->id, $low->id], $this->shown());
    }

    /* ---------------------------------------------------------------- */
    /* الأداء */
    /* ---------------------------------------------------------------- */

    public function test_showing_an_ad_counts_an_impression(): void
    {
        $ad = $this->ad();

        $this->get('/ar/properties');
        $this->get('/ar/properties');

        $this->assertSame(2, $ad->fresh()->impressions);
    }

    public function test_a_hidden_ad_counts_nothing(): void
    {
        $ad = $this->ad(['starts_at' => now()->addWeek()]);

        $this->get('/ar/properties');

        $this->assertSame(0, $ad->fresh()->impressions);
    }

    public function test_the_click_link_counts_and_forwards(): void
    {
        $ad = $this->ad();

        $this->get("/ar/ads/{$ad->id}")
            ->assertRedirect("/ar/properties/{$this->unit->slug}");

        $this->assertSame(1, $ad->fresh()->clicks);
    }

    public function test_a_click_on_a_dead_ad_still_lands_somewhere_useful(): void
    {
        // إعلان اتمسح — الزائر يروح القايمة مش صفحة 404
        $this->get('/ar/ads/9999')->assertRedirect('/ar/properties');
    }

    public function test_click_through_rate_is_computed_from_real_numbers(): void
    {
        $ad = $this->ad(['impressions' => 200, 'clicks' => 5]);

        $this->assertSame(2.5, $ad->ctr());
        $this->assertSame(0.0, $this->ad(['impressions' => 0])->ctr());
    }

    /* ---------------------------------------------------------------- */
    /* اللوحة */
    /* ---------------------------------------------------------------- */

    public function test_only_marketing_and_admins_reach_the_screen(): void
    {
        $this->actingAs($this->userWithRole('marketing'))->get('/admin/featured-ads')->assertOk();
        $this->actingAs($this->userWithRole('admin'))->get('/admin/featured-ads')->assertOk();

        $this->actingAs($this->userWithRole('data_entry'))->get('/admin/featured-ads')->assertForbidden();
        $this->actingAs($this->userWithRole('editor'))->get('/admin/featured-ads')->assertForbidden();
    }

    public function test_an_ad_must_point_at_exactly_one_thing(): void
    {
        $this->actingAs($this->userWithRole('marketing'));

        $this->post('/admin/featured-ads', [
            'position' => 'listing', 'status' => 'approved', 'is_active' => true,
        ])->assertSessionHasErrors('property_id');

        $compound = Compound::create(['name' => 'مشروع']);

        $this->post('/admin/featured-ads', [
            'position' => 'listing', 'status' => 'approved', 'is_active' => true,
            'property_id' => $this->unit->id, 'compound_id' => $compound->id,
        ])->assertSessionHasErrors('property_id');

        $this->assertSame(0, FeaturedAd::count());
    }

    public function test_the_window_has_to_make_sense(): void
    {
        $this->actingAs($this->userWithRole('marketing'))
            ->post('/admin/featured-ads', [
                'position' => 'listing', 'status' => 'approved', 'is_active' => true,
                'property_id' => $this->unit->id,
                'starts_at' => '2026-06-01', 'ends_at' => '2026-01-01',
            ])->assertSessionHasErrors('ends_at');
    }

    /* ---------------------------------------------------------------- */
    /* طلب المعلن */
    /* ---------------------------------------------------------------- */

    public function test_an_owner_can_ask_for_a_slot(): void
    {
        $lister = $this->userWithRole('lister');
        $this->unit->update(['owner_id' => $lister->id]);

        $this->actingAs($lister)
            ->post("/ar/account/my-properties/{$this->unit->id}/feature")
            ->assertRedirect()
            ->assertSessionHas('success');

        $ad = FeaturedAd::sole();

        // بيتعمل طلب مش إعلان شغّال — المعلن مايحجزش المساحة لنفسه
        $this->assertSame('pending', $ad->status);
        $this->assertSame($lister->id, $ad->requested_by);
        $this->assertSame([], $this->shown());
    }

    public function test_a_second_request_on_the_same_unit_is_refused(): void
    {
        $lister = $this->userWithRole('lister');
        $this->unit->update(['owner_id' => $lister->id]);

        $this->actingAs($lister);
        $this->post("/ar/account/my-properties/{$this->unit->id}/feature");
        $this->post("/ar/account/my-properties/{$this->unit->id}/feature")->assertSessionHas('error');

        $this->assertSame(1, FeaturedAd::count());
    }

    public function test_a_unit_still_under_review_cannot_be_promoted(): void
    {
        $lister = $this->userWithRole('lister');
        $this->unit->update(['owner_id' => $lister->id, 'status' => 'pending']);

        $this->actingAs($lister)
            ->post("/ar/account/my-properties/{$this->unit->id}/feature")
            ->assertSessionHas('error');

        $this->assertSame(0, FeaturedAd::count());
    }

    public function test_nobody_can_promote_someone_elses_unit(): void
    {
        $lister = $this->userWithRole('lister');
        $this->unit->update(['owner_id' => $this->userWithRole('broker')->id]);

        $this->actingAs($lister)
            ->post("/ar/account/my-properties/{$this->unit->id}/feature")
            ->assertNotFound();

        $this->assertSame(0, FeaturedAd::count());
    }

    public function test_the_owner_sees_where_the_request_got_to(): void
    {
        $lister = $this->userWithRole('lister');
        $this->unit->update(['owner_id' => $lister->id]);

        $this->actingAs($lister)->post("/ar/account/my-properties/{$this->unit->id}/feature");

        $row = $this->get('/ar/account/my-properties')->viewData('page')['props']['properties'][0];

        $this->assertTrue($row['promotion']['open']);
        $this->assertSame('في انتظار الموافقة', $row['promotion']['state']['label']);
    }
}
