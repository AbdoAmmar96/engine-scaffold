<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Leads\Models\Lead;
use Modules\Properties\Models\Property;
use Tests\TestCase;

/**
 * «وحداتي» — مساحة المعلن على الموقع.
 *
 * الخطرين اللي الاختبارات دي بتقفلهم:
 *   1. حد يفتح وحدة مش بتاعته بالرقم في العنوان.
 *   2. المعلن ينشر على الموقع من غير مراجعة.
 */
class MyListingsTest extends TestCase
{
    use RefreshDatabase;

    private User $lister;

    private User $other;

    private Property $mine;

    private Property $theirs;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);

        $this->lister = $this->userWithRole('lister', 'lister@test.local');
        $this->other = $this->userWithRole('broker', 'broker@test.local');

        $this->mine = $this->unit('MINE', $this->lister->id);
        $this->theirs = $this->unit('THEIRS', $this->other->id);
    }

    private function userWithRole(string $role, string $email): User
    {
        $user = User::create(['name' => $role, 'email' => $email, 'password' => 'password123']);
        $user->syncRoles([$role]);

        return $user;
    }

    private function unit(string $ref, ?int $owner = null, array $extra = []): Property
    {
        return Property::create(array_merge([
            'title' => "وحدة {$ref}",
            'ref' => $ref,
            'purpose' => 'sale',
            'type' => 'شقة',
            'status' => 'published',
            'is_active' => true,
            'owner_id' => $owner,
        ], $extra));
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'title' => 'شقة جديدة',
            'purpose' => 'sale',
            'type' => 'شقة',
            'price_amount' => 3_000_000,
            'size' => 140,
        ], $extra);
    }

    /* ---------------------------------------------------------------- */

    public function test_a_lister_only_sees_their_own_units(): void
    {
        $rows = $this->actingAs($this->lister)
            ->get('/ar/account/my-properties')
            ->viewData('page')['props']['properties'];

        $this->assertSame(['MINE'], array_column($rows, 'ref'));
    }

    public function test_a_customer_has_no_listing_area(): void
    {
        $customer = $this->userWithRole('customer', 'customer@test.local');

        $this->actingAs($customer)->get('/ar/account/my-properties')->assertForbidden();
    }

    public function test_a_guest_is_sent_to_the_site_login(): void
    {
        $this->get('/ar/account/my-properties')->assertRedirect('/ar/login');
    }

    public function test_another_owners_unit_is_a_404_not_a_403(): void
    {
        $this->actingAs($this->lister);

        // 404 مش 403: مش لازم يعرف إن الرقم ده موجود أصلًا
        $this->get("/ar/account/my-properties/{$this->theirs->id}/edit")->assertNotFound();
        $this->put("/ar/account/my-properties/{$this->theirs->id}", $this->payload())->assertNotFound();
        $this->post("/ar/account/my-properties/{$this->theirs->id}/toggle")->assertNotFound();
        $this->delete("/ar/account/my-properties/{$this->theirs->id}")->assertNotFound();

        $this->assertModelExists($this->theirs);
        $this->assertSame('وحدة THEIRS', $this->theirs->fresh()->title);
    }

    public function test_a_new_unit_belongs_to_its_creator_and_waits_for_review(): void
    {
        $this->actingAs($this->lister)
            ->post('/ar/account/my-properties', $this->payload([
                'status' => 'published',
                'is_featured' => true,
                'owner_id' => $this->other->id,
            ]))
            ->assertRedirect('/ar/account/my-properties');

        $property = Property::where('title', 'شقة جديدة')->sole();

        $this->assertSame($this->lister->id, $property->owner_id);
        $this->assertSame('pending', $property->status);
        $this->assertFalse($property->is_featured);
        $this->assertNull($property->ref);
    }

    public function test_editing_a_live_unit_sends_it_back_to_review(): void
    {
        $this->actingAs($this->lister)
            ->put("/ar/account/my-properties/{$this->mine->id}", $this->payload(['title' => 'العنوان اتغيّر']))
            ->assertRedirect();

        $this->mine->refresh();

        $this->assertSame('العنوان اتغيّر', $this->mine->title);
        $this->assertSame('pending', $this->mine->status);
    }

    public function test_a_sold_unit_stays_sold_when_edited(): void
    {
        $sold = $this->unit('SOLD', $this->lister->id, ['status' => 'sold']);

        $this->actingAs($this->lister)
            ->put("/ar/account/my-properties/{$sold->id}", $this->payload(['title' => 'تعديل']));

        $this->assertSame('sold', $sold->fresh()->status);
    }

    public function test_hiding_a_unit_takes_it_off_the_site_without_touching_review(): void
    {
        $this->actingAs($this->lister)
            ->post("/ar/account/my-properties/{$this->mine->id}/toggle")
            ->assertRedirect();

        $this->mine->refresh();

        $this->assertFalse($this->mine->is_active);
        $this->assertSame('published', $this->mine->status);
        $refs = array_column($this->get('/ar/properties')->viewData('page')['props']['properties'], 'ref');
        $this->assertNotContains('MINE', $refs);
    }

    public function test_photos_can_be_added_and_dropped_on_edit(): void
    {
        $unit = $this->unit('PIX', $this->lister->id, [
            'image' => '/storage/uploads/listings/one.jpg',
            'gallery' => "/storage/uploads/listings/two.jpg\n/storage/uploads/listings/three.jpg",
        ]);

        $this->actingAs($this->lister)->put("/ar/account/my-properties/{$unit->id}", $this->payload([
            // شال التانية، وساب الأولى والتالتة، وضاف واحدة
            'keep' => ['/storage/uploads/listings/one.jpg', '/storage/uploads/listings/three.jpg'],
            'images' => [UploadedFile::fake()->image('four.jpg')],
        ]));

        $paths = $unit->fresh()->imagePaths();

        $this->assertCount(3, $paths);
        $this->assertSame('/storage/uploads/listings/one.jpg', $paths[0]);
        $this->assertNotContains('/storage/uploads/listings/two.jpg', $paths);
    }

    public function test_photos_survive_an_edit_that_does_not_mention_them(): void
    {
        $unit = $this->unit('KEEP', $this->lister->id, ['image' => '/storage/uploads/listings/one.jpg']);

        $this->actingAs($this->lister)->put("/ar/account/my-properties/{$unit->id}", $this->payload());

        $this->assertSame(['/storage/uploads/listings/one.jpg'], $unit->fresh()->imagePaths());
    }

    public function test_the_summary_counts_views_saves_and_requests(): void
    {
        Property::recordView($this->mine->id);
        Property::recordView($this->mine->id);

        $this->lister->favorites()->attach($this->mine->id);
        Lead::create(['name' => 'مهتم', 'phone' => '1', 'property_id' => $this->mine->id, 'source' => 'property', 'status' => 'new']);

        $props = $this->actingAs($this->lister)->get('/ar/account/my-properties')->viewData('page')['props'];

        $this->assertSame(2, $props['properties'][0]['views']);
        $this->assertSame(1, $props['properties'][0]['saves']);
        $this->assertSame(1, $props['properties'][0]['requests']);
        $this->assertSame(2, $props['summary']['views']);
        $this->assertSame(1, $props['summary']['leads']);
    }

    public function test_opening_a_unit_page_counts_a_view(): void
    {
        $this->get("/ar/properties/{$this->mine->slug}")->assertOk();

        $this->assertSame(1, $this->mine->fresh()->views_count);
    }

    public function test_counting_a_view_does_not_touch_the_sitemap_timestamp(): void
    {
        $before = $this->mine->updated_at;

        Property::recordView($this->mine->id);

        // lastmod في خريطة الموقع بيتبني على updated_at — الزيارة مش تعديل
        $this->assertEquals($before, $this->mine->fresh()->updated_at);
    }

    public function test_a_lister_can_delete_their_own_unit(): void
    {
        $this->actingAs($this->lister)
            ->delete("/ar/account/my-properties/{$this->mine->id}")
            ->assertRedirect('/ar/account/my-properties');

        $this->assertModelMissing($this->mine);
    }

    public function test_the_overview_links_to_the_listings_for_owners_only(): void
    {
        $customer = $this->userWithRole('customer', 'customer@test.local');

        $this->assertSame(1, $this->actingAs($this->lister)->get('/ar/account')->viewData('page')['props']['stats']['listings']);
        $this->assertNull($this->actingAs($customer)->get('/ar/account')->viewData('page')['props']['stats']['listings']);
    }
}
