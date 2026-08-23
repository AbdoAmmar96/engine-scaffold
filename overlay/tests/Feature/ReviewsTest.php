<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Reviews\Models\Review;
use Tests\TestCase;

/**
 * آراء العملاء.
 *
 * التقييمات أكتر محتوى بيتزوّر في المواقع العقارية، فالاختبارات هنا
 * بتحرس حاجتين: **مفيش رأي بيظهر قبل المراجعة**، و**القسم بيختفي وهو
 * فاضي** بدل ما يتحط فيه كلام متلفّق. وأي تعديل من العميل بيرجّع الرأي
 * للمراجعة — نص اتعتمد ممنوع يتغيّر من ورا الأدمن.
 */
class ReviewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(string $role, string $name = 'عميل'): User
    {
        $user = User::create([
            'name' => $name,
            'email' => "{$role}@test.local",
            'password' => 'password123',
        ]);

        $user->syncRoles([$role]);

        return $user;
    }

    private function review(array $extra = []): Review
    {
        return Review::create(array_merge([
            'author' => 'منى عبد العزيز',
            'body' => 'اشتريت من خلالهم وكل بيان كان مطابق للواقع.',
            'rating' => 5,
            'status' => 'published',
            'published_at' => now(),
        ], $extra));
    }

    /* ---------------- العرض ---------------- */

    public function test_the_home_page_shows_nothing_when_there_are_no_reviews(): void
    {
        // القسم الفاضي أصدق من رأي متلفّق — ومفيش سيدر بيزرع آراء
        $this->assertSame([], Catalog::reviews('ar'));

        $this->get('/ar')->assertOk()
            ->assertInertia(fn ($p) => $p->where('reviews', []));
    }

    public function test_only_approved_reviews_reach_the_site(): void
    {
        $this->review(['author' => 'منشور']);
        $this->review(['author' => 'تحت المراجعة', 'status' => 'pending']);
        $this->review(['author' => 'مرفوض', 'status' => 'rejected']);

        $authors = array_column(Catalog::reviews('ar'), 'author');

        $this->assertSame(['منشور'], $authors);
    }

    public function test_english_falls_back_to_arabic_when_untranslated(): void
    {
        $this->review(['author_en' => 'Mona A.', 'body_en' => null]);

        $card = Catalog::reviews('en')[0];

        $this->assertSame('Mona A.', $card['author']);
        $this->assertSame('اشتريت من خلالهم وكل بيان كان مطابق للواقع.', $card['body']);
    }

    /* ---------------- العميل بيكتب رأيه ---------------- */

    public function test_a_guest_cannot_open_the_review_form(): void
    {
        $this->get('/ar/account/review')->assertRedirect();
    }

    public function test_a_customer_review_lands_under_review_not_on_the_site(): void
    {
        $user = $this->userWithRole('customer', 'سامي');

        $this->actingAs($user)->post('/ar/account/review', [
            'body' => 'تعامل محترم والمعاينة كانت في ميعادها بالظبط.',
            'rating' => 4,
        ])->assertRedirect()->assertSessionHas('success');

        $review = Review::sole();

        $this->assertSame('pending', $review->status);
        $this->assertSame('site', $review->source);
        $this->assertSame($user->id, $review->user_id);
        $this->assertSame('سامي', $review->author);
        $this->assertSame(4, $review->rating);

        // ولسه مش على الموقع
        $this->assertSame([], Catalog::reviews('ar'));
    }

    public function test_one_review_per_account_editing_replaces_it(): void
    {
        $user = $this->userWithRole('customer');

        $this->actingAs($user)->post('/ar/account/review', [
            'body' => 'الرأي الأول اللي كتبته عن التجربة دي.',
            'rating' => 3,
        ]);

        $this->actingAs($user)->post('/ar/account/review', [
            'body' => 'غيّرت رأيي بعد ما خلّصت الإجراءات كلها.',
            'rating' => 5,
        ]);

        $this->assertSame(1, Review::count());
        $this->assertSame(5, Review::sole()->rating);
    }

    public function test_editing_an_approved_review_sends_it_back_for_review(): void
    {
        $user = $this->userWithRole('customer');

        $review = $this->review(['user_id' => $user->id, 'source' => 'site']);
        $this->assertSame('published', $review->status);

        $this->actingAs($user)->post('/ar/account/review', [
            'body' => 'نص جديد خالص اتكتب بعد ما الرأي كان اتعتمد.',
            'rating' => 1,
        ]);

        // نص اتعتمد ممنوع يتغيّر على الموقع من ورا الأدمن
        $this->assertSame('pending', $review->fresh()->status);
        $this->assertNull($review->fresh()->published_at);
        $this->assertSame([], Catalog::reviews('ar'));
    }

    public function test_a_too_short_review_is_refused(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->post('/ar/account/review', ['body' => 'حلو', 'rating' => 5])
            ->assertSessionHasErrors('body');

        $this->assertSame(0, Review::count());
    }

    public function test_the_rating_stays_between_one_and_five(): void
    {
        $user = $this->userWithRole('customer');

        foreach ([0, 6, -3] as $bad) {
            $this->actingAs($user)
                ->post('/ar/account/review', ['body' => 'نص طويل كفاية عشان يعدّي التحقق.', 'rating' => $bad])
                ->assertSessionHasErrors('rating');
        }

        $this->assertSame(0, Review::count());
    }

    /* ---------------- اللوحة ---------------- */

    public function test_the_admin_screen_is_gated_on_managing_content(): void
    {
        $this->actingAs($this->userWithRole('editor'))->get('/admin/reviews')->assertOk();
        $this->actingAs($this->userWithRole('data_entry'))->get('/admin/reviews')->assertForbidden();
    }

    public function test_approving_a_review_puts_it_on_the_site_and_stamps_it(): void
    {
        $review = $this->review(['status' => 'pending', 'published_at' => null]);

        $this->actingAs($this->userWithRole('editor'))
            ->put("/admin/reviews/{$review->id}", [
                'author' => $review->author,
                'body' => $review->body,
                'rating' => 5,
                'status' => 'published',
            ])->assertRedirect();

        $review->refresh();

        $this->assertSame('published', $review->status);
        // التاريخ بيتحط ساعة الاعتماد — الترتيب على الموقع بترتيب الاعتماد
        $this->assertNotNull($review->published_at);
        $this->assertCount(1, Catalog::reviews('ar'));
    }

    public function test_the_admin_cannot_invent_a_source_or_a_status(): void
    {
        $review = $this->review();

        $this->actingAs($this->userWithRole('editor'))
            ->put("/admin/reviews/{$review->id}", [
                'author' => $review->author,
                'body' => $review->body,
                'source' => 'verified-by-us',
                'status' => 'featured',
            ])->assertSessionHasErrors(['source', 'status']);
    }
}
