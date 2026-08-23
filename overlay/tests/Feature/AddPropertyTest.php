<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Leads\Models\Lead;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;
use Modules\Properties\Notifications\ListingReceivedNotification;
use Tests\TestCase;

/**
 * فورم «أضف عقارك».
 *
 * الخطر هنا إن مسار عام يقدر ينشر على الموقع. كل اختبار تحت بيتأكد
 * إن اللي بيتعمل بيقف عند «في انتظار المراجعة» مهما اتبعت في الفورم.
 */
class AddPropertyTest extends TestCase
{
    use RefreshDatabase;

    private Location $area;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);

        $this->area = Location::create(['name' => 'القاهرة الجديدة', 'name_en' => 'New Cairo']);
    }

    /** @return array<string, mixed> */
    private function payload(array $extra = []): array
    {
        return array_merge([
            'name' => 'سامي عبد الله',
            'phone' => '01000000000',
            'email' => 'sami@example.com',
            'title' => 'شقة ١٥٠م بحديقة',
            'purpose' => 'sale',
            'type' => 'شقة',
            'location_id' => $this->area->id,
            'price_amount' => 4_500_000,
            'size' => 150,
            'beds' => 3,
            'baths' => 2,
            'finishing' => 'full',
            'description' => 'إطلالة على اللاندسكيب.',
        ], $extra);
    }

    public function test_it_creates_a_unit_waiting_for_review(): void
    {
        $this->post('/ar/add-property', $this->payload())
            ->assertRedirect()
            ->assertSessionHas('success');

        $property = Property::sole();

        $this->assertSame('pending', $property->status);
        $this->assertSame('شقة ١٥٠م بحديقة', $property->title);
        $this->assertSame(4_500_000, $property->price_amount);
        $this->assertSame($this->area->id, $property->location_id);

        // الاعتماد هو اللي بيولّد الكود — لسه مجاش
        $this->assertNull($property->ref);
        $this->assertNull($property->published_at);
    }

    public function test_the_new_unit_is_not_on_the_site_yet(): void
    {
        $this->post('/ar/add-property', $this->payload());

        $response = $this->get('/ar/properties');

        $this->assertSame([], $response->viewData('page')['props']['properties']);
    }

    public function test_the_form_cannot_publish_itself(): void
    {
        $this->post('/ar/add-property', $this->payload([
            'status' => 'published',
            'is_featured' => true,
            'ref' => 'BP-9999',
        ]));

        $property = Property::sole();

        $this->assertSame('pending', $property->status);
        $this->assertFalse($property->is_featured);
        $this->assertNull($property->ref);
    }

    public function test_it_opens_a_lead_linked_to_the_unit(): void
    {
        $this->post('/ar/add-property', $this->payload());

        $lead = Lead::sole();

        $this->assertSame('listing', $lead->source);
        $this->assertSame('new', $lead->status);
        $this->assertSame('سامي عبد الله', $lead->name);
        $this->assertSame(Property::sole()->id, $lead->property_id);
        $this->assertStringContainsString('شقة ١٥٠م بحديقة', $lead->message);
    }

    public function test_photos_are_stored_and_the_first_one_leads(): void
    {
        $this->post('/ar/add-property', $this->payload([
            'images' => [
                UploadedFile::fake()->image('front.jpg'),
                UploadedFile::fake()->image('salon.jpg'),
            ],
        ]));

        $property = Property::sole();

        $this->assertStringStartsWith('/storage/uploads/listings/', $property->image);
        $this->assertCount(1, Property::lines($property->gallery));
        $this->assertCount(2, Storage::disk('public')->files('uploads/listings'));
    }

    public function test_it_refuses_files_that_are_not_images(): void
    {
        $this->post('/ar/add-property', $this->payload([
            'images' => [UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf')],
        ]))->assertSessionHasErrors('images.0');

        $this->assertSame(0, Property::count());
    }

    public function test_a_broker_owns_what_they_submit(): void
    {
        $broker = User::create(['name' => 'وسيط', 'email' => 'broker@example.com', 'password' => 'secret-pass']);
        $broker->assignRole('broker');

        $this->actingAs($broker)->post('/ar/add-property', $this->payload());

        $this->assertSame($broker->id, Property::sole()->owner_id);
        $this->assertSame($broker->id, Lead::sole()->owner_id);
    }

    public function test_a_customer_who_lists_a_unit_becomes_a_lister(): void
    {
        $customer = User::create(['name' => 'عميل', 'email' => 'customer@example.com', 'password' => 'secret-pass']);
        $customer->assignRole('customer');

        $this->actingAs($customer)->post('/ar/add-property', $this->payload());

        // من غير الترقية دي الوحدة بتتسجّل باسمه ومايقدرش يتابعها من حسابه
        $this->assertTrue($customer->fresh()->hasRole('lister'));
        $this->assertSame($customer->id, Property::sole()->owner_id);

        // مالك الوحدة مش «عميل قدّم طلب» — الطلب بيوصله في صندوقه
        $this->assertNull(Lead::sole()->user_id);
        $this->assertSame($customer->id, Lead::sole()->owner_id);
    }

    /* ---------------- الزائر بياخد حساب ---------------- */

    public function test_a_guest_gets_an_account_that_owns_the_unit(): void
    {
        Notification::fake();

        $this->post('/ar/add-property', $this->payload());

        $user = User::where('email', 'sami@example.com')->sole();

        // من غير ده الوحدة بتبقى يتيمة والصفحة بتوعده بمتابعة مستحيلة
        $this->assertTrue($user->hasRole('lister'));
        $this->assertSame($user->id, Property::sole()->owner_id);
        $this->assertSame($user->id, Lead::sole()->owner_id);
        $this->assertSame('01000000000', $user->phone);

        // مالك الوحدة مش «عميل قدّم طلب»
        $this->assertNull(Lead::sole()->user_id);
    }

    public function test_the_owner_actually_finds_the_unit_after_signing_in(): void
    {
        Notification::fake();

        $this->post('/ar/add-property', $this->payload());

        $refs = $this->actingAs(User::where('email', 'sami@example.com')->sole())
            ->get('/ar/account/my-properties')
            ->assertOk()
            ->viewData('page')['props']['properties'];

        // ده الوعد اللي الصفحة بتقوله — لازم يتنفّذ فعلًا
        $this->assertCount(1, $refs);
        $this->assertSame('شقة ١٥٠م بحديقة', $refs[0]['title']);
    }

    public function test_the_guest_is_emailed_a_link_to_set_a_password(): void
    {
        Notification::fake();

        $this->post('/ar/add-property', $this->payload());

        Notification::assertSentTo(
            User::where('email', 'sami@example.com')->sole(),
            ListingReceivedNotification::class,
        );
    }

    public function test_the_new_account_cannot_be_entered_without_that_link(): void
    {
        Notification::fake();

        $this->post('/ar/add-property', $this->payload());

        // كلمة السر عشوائية — الحساب مايتفتحش غير باللينك اللي في الإيميل،
        // فحد بعت بإيميل مش بتاعه مش بياخد وصول لحاجة
        foreach (['', 'password', '01000000000', 'sami@example.com'] as $guess) {
            $this->assertFalse(Auth::attempt(['email' => 'sami@example.com', 'password' => $guess]));
        }
    }

    public function test_a_second_submission_reuses_the_same_account(): void
    {
        Notification::fake();

        $this->post('/ar/add-property', $this->payload());
        $this->post('/ar/add-property', $this->payload(['title' => 'فيلا ٣٠٠م']));

        $this->assertSame(1, User::where('email', 'sami@example.com')->count());
        $this->assertSame(2, Property::where('owner_id', User::where('email', 'sami@example.com')->value('id'))->count());
    }

    public function test_an_existing_account_is_used_as_is_and_told_about_it(): void
    {
        Notification::fake();

        $existing = User::create([
            'name' => 'صاحب الحساب',
            'email' => 'sami@example.com',
            'password' => 'a-password-they-chose',
        ]);
        $existing->assignRole('lister');

        $this->post('/ar/add-property', $this->payload());

        $this->assertSame(1, User::where('email', 'sami@example.com')->count());
        $this->assertSame($existing->id, Property::sole()->owner_id);

        // كلمة سره ما اتلمستش — الوحدة بتتحط على حسابه وهو بيتبلّغ
        $this->assertTrue(Auth::attempt(['email' => 'sami@example.com', 'password' => 'a-password-they-chose']));
        Notification::assertSentTo($existing->fresh(), ListingReceivedNotification::class);
    }

    public function test_the_form_does_not_reveal_whether_the_email_has_an_account(): void
    {
        Notification::fake();

        $first = $this->post('/ar/add-property', $this->payload())->assertSessionHas('success');

        $second = $this->post('/ar/add-property', $this->payload(['title' => 'وحدة تانية']))
            ->assertSessionHas('success');

        // نفس الرسالة في الحالتين، وإلا الفورم بيبقى أداة يعرف بيها
        // أي حد مين عنده حساب هنا
        $this->assertSame(
            $first->getSession()->get('success'),
            $second->getSession()->get('success'),
        );
    }

    public function test_the_email_is_required_because_it_picks_the_account(): void
    {
        $this->post('/ar/add-property', $this->payload(['email' => null]))
            ->assertSessionHasErrors('email');

        $this->assertSame(0, Property::count());
        $this->assertSame(0, User::count());
    }

    public function test_the_honeypot_swallows_bots_without_writing_anything(): void
    {
        $this->post('/ar/add-property', $this->payload(['website' => 'http://spam.example']))
            ->assertSessionHas('success');

        $this->assertSame(0, Property::count());
        $this->assertSame(0, Lead::count());
    }

    public function test_the_essentials_are_required(): void
    {
        $this->post('/ar/add-property', [])
            ->assertSessionHasErrors(['name', 'phone', 'email', 'title', 'purpose', 'type']);

        $this->assertSame(0, Property::count());
    }

    public function test_it_refuses_a_type_that_is_not_on_the_list(): void
    {
        $this->post('/ar/add-property', $this->payload(['type' => 'قصر']))
            ->assertSessionHasErrors('type');

        $this->assertSame(0, Property::count());
    }

    public function test_the_page_renders_with_its_options(): void
    {
        $response = $this->get('/ar/add-property');

        $response->assertOk();
        $props = $response->viewData('page')['props'];

        $this->assertSame('Site/AddProperty', $response->viewData('page')['component']);
        $this->assertCount(count(Property::TYPES), $props['options']['types']);
        $this->assertSame([['value' => (string) $this->area->id, 'label' => 'القاهرة الجديدة']], $props['options']['locations']);
    }
}
