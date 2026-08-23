<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;
use Modules\Properties\Models\SavedSearch;
use Modules\Properties\Notifications\NewMatchesNotification;
use Tests\TestCase;

/**
 * البحث المحفوظ + التنبيهات.
 *
 * أخطر حاجة هنا التنبيه المكرّر: العميل اللي بياخد نفس الوحدة كل يوم
 * بيقفل التنبيه، فمعظم الاختبارات على العلامة اللي بتمنع التكرار.
 */
class SavedSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Location $cairo;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->seed(RolePermissionSeeder::class);

        $this->cairo = Location::create(['name' => 'القاهرة الجديدة', 'name_en' => 'New Cairo']);

        $this->user = User::create(['name' => 'عميل', 'email' => 'user@test.local', 'password' => 'password123']);
        $this->user->syncRoles(['customer']);
    }

    private function unit(string $ref, array $extra = []): Property
    {
        return Property::create(array_merge([
            'title' => "وحدة {$ref}", 'ref' => $ref, 'purpose' => 'sale', 'type' => 'شقة',
            'status' => 'published', 'is_active' => true, 'location_id' => $this->cairo->id,
        ], $extra));
    }

    private function save(array $query = ['type' => 'شقة', 'purpose' => 'sale']): SavedSearch
    {
        $this->actingAs($this->user)
            ->post('/ar/account/saved-searches?'.http_build_query($query))
            ->assertRedirect();

        return SavedSearch::latest('id')->first();
    }

    /* ---------------------------------------------------------------- */

    public function test_it_saves_the_filters_that_were_applied(): void
    {
        $search = $this->save(['type' => 'شقة', 'purpose' => 'rent', 'price_max' => 5_000_000]);

        $this->assertSame($this->user->id, $search->user_id);
        $this->assertSame('شقة', $search->filters['type']);
        $this->assertSame('rent', $search->filters['purpose']);
        $this->assertSame(5000000, $search->filters['price_max']);
    }

    public function test_unknown_query_keys_never_reach_the_database(): void
    {
        $search = $this->save(['type' => 'شقة', 'evil' => 'drop table', 'order_by' => 'password']);

        // نفس حارس Catalog::filters — المفتاح المش معروف بيتجاهل
        $this->assertSame(['type'], array_keys($search->filters));
    }

    public function test_an_empty_search_is_refused(): void
    {
        $this->actingAs($this->user)
            ->post('/ar/account/saved-searches')
            ->assertSessionHas('error');

        $this->assertSame(0, SavedSearch::count());
    }

    public function test_it_names_itself_from_the_filters(): void
    {
        $search = $this->save(['type' => 'شقة', 'purpose' => 'sale']);

        $this->assertStringContainsString('شقة', $search->name);
        $this->assertStringContainsString('بيع', $search->name);
    }

    public function test_a_guest_cannot_save_a_search(): void
    {
        $this->post('/ar/account/saved-searches?type=شقة')->assertRedirect('/ar/login');

        $this->assertSame(0, SavedSearch::count());
    }

    public function test_searches_are_private_to_their_owner(): void
    {
        $search = $this->save();

        $other = User::create(['name' => 'تاني', 'email' => 'other@test.local', 'password' => 'password123']);
        $other->syncRoles(['customer']);

        $this->actingAs($other);
        $this->put("/ar/account/saved-searches/{$search->id}", ['name' => 'مسروق', 'alerts' => false])->assertNotFound();
        $this->delete("/ar/account/saved-searches/{$search->id}")->assertNotFound();

        $this->assertSame($this->user->id, $search->fresh()->user_id);
    }

    public function test_the_limit_stops_runaway_saving(): void
    {
        for ($i = 0; $i < SavedSearch::LIMIT; $i++) {
            $this->user->savedSearches()->create(['name' => "بحث {$i}", 'filters' => ['type' => 'شقة']]);
        }

        $this->actingAs($this->user)
            ->post('/ar/account/saved-searches?type=فيلا')
            ->assertSessionHas('error');

        $this->assertSame(SavedSearch::LIMIT, SavedSearch::count());
    }

    /* ---------------------------------------------------------------- */
    /* التنبيهات */
    /* ---------------------------------------------------------------- */

    public function test_only_units_added_after_the_save_count_as_new(): void
    {
        $this->unit('OLD');
        $search = $this->save();

        // اللي كان موجود وقت الحفظ العميل شافه في الصفحة — مش جديد
        $this->artisan('searches:alert')->assertSuccessful();
        Notification::assertNothingSent();

        $this->unit('NEW');
        $this->artisan('searches:alert')->assertSuccessful();

        Notification::assertSentTo($this->user, NewMatchesNotification::class);
    }

    public function test_the_same_unit_is_never_sent_twice(): void
    {
        $search = $this->save();
        $this->unit('NEW');

        $this->artisan('searches:alert');
        Notification::assertSentToTimes($this->user, NewMatchesNotification::class, 1);

        $this->artisan('searches:alert');
        Notification::assertSentToTimes($this->user, NewMatchesNotification::class, 1);

        $this->assertNotNull($search->fresh()->last_alert_at);
    }

    public function test_a_unit_that_does_not_match_triggers_nothing(): void
    {
        $this->save(['type' => 'فيلا']);
        $this->unit('APARTMENT');

        $this->artisan('searches:alert');

        Notification::assertNothingSent();
    }

    public function test_a_pending_unit_does_not_trigger_an_alert(): void
    {
        $this->save();
        $this->unit('PENDING', ['status' => 'pending']);

        $this->artisan('searches:alert');

        Notification::assertNothingSent();
    }

    public function test_switching_alerts_off_stops_the_emails(): void
    {
        $search = $this->save();

        $this->actingAs($this->user)->put("/ar/account/saved-searches/{$search->id}", [
            'name' => $search->name,
            'alerts' => false,
        ])->assertRedirect();

        $this->unit('NEW');
        $this->artisan('searches:alert');

        Notification::assertNothingSent();
    }

    public function test_a_suspended_account_gets_no_alerts(): void
    {
        $this->save();
        $this->user->update(['is_active' => false]);
        $this->unit('NEW');

        $this->artisan('searches:alert');

        Notification::assertNothingSent();
    }

    public function test_the_page_shows_the_live_match_count(): void
    {
        $this->unit('A');
        $this->unit('B');
        $this->save();

        $rows = $this->actingAs($this->user)
            ->get('/ar/account/saved-searches')
            ->viewData('page')['props']['searches'];

        $this->assertCount(1, $rows);
        $this->assertSame(2, $rows[0]['matches']);
        $this->assertTrue($rows[0]['alerts']);
        $this->assertNotEmpty($rows[0]['summary']);
    }
}
