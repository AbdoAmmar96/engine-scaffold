<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ReservedSlugs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Pages\Models\Page;
use Tests\TestCase;

/**
 * صفحات المحتوى على `/{locale}/{slug}`.
 *
 * الخطر الأساسي في التصميم ده إن الصفحة تخطف مسار موجود: صفحة اسمها
 * «العقارات» بتولّد slug اسمه `properties`، وتتحفظ بنجاح، والأدمن يفتحها
 * ويلاقي صفحة الوحدات — من غير أي رسالة خطأ. الاختبارات هنا بتحرس
 * الاتجاهين: الراوت الحقيقي بيغلب، والـ slug المحجوز بيترفض من الأساس.
 */
class ContentPagesTest extends TestCase
{
    use RefreshDatabase;

    private function page(array $extra = []): Page
    {
        return Page::create(array_merge([
            'title' => 'سياسة الخصوصية',
            'title_en' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'body' => "أول فقرة.\n\n## عنوان فرعي\n- نقطة أولى\n- نقطة تانية",
            'is_active' => true,
        ], $extra));
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

    /* ---------------- العرض ---------------- */

    public function test_a_published_page_renders_in_both_locales(): void
    {
        $this->page();

        $this->get('/ar/privacy-policy')->assertOk()
            ->assertInertia(fn ($p) => $p->component('Site/Page')->where('page.title', 'سياسة الخصوصية'));

        $this->get('/en/privacy-policy')->assertOk()
            ->assertInertia(fn ($p) => $p->where('page.title', 'Privacy Policy'));
    }

    public function test_english_falls_back_to_arabic_when_untranslated(): void
    {
        $this->page(['title_en' => null, 'body_en' => null]);

        // نص عربي في صفحة إنجليزي أحسن من صفحة فاضية
        $this->get('/en/privacy-policy')->assertOk()
            ->assertInertia(fn ($p) => $p->where('page.title', 'سياسة الخصوصية'));
    }

    public function test_a_draft_page_is_not_public(): void
    {
        $this->page(['is_active' => false]);

        $this->get('/ar/privacy-policy')->assertNotFound();
    }

    public function test_an_unknown_slug_is_a_404_not_a_blank_page(): void
    {
        $this->get('/ar/nothing-here')->assertNotFound();
    }

    /* ---------------- مساحة الأسماء المشتركة ---------------- */

    public function test_a_real_route_wins_over_a_page_with_the_same_slug(): void
    {
        // الحفظ المباشر بيتخطّى تحقق اللوحة — ده بيقيس ترتيب الراوتات نفسه
        Page::withoutEvents(fn () => Page::create([
            'title' => 'العقارات', 'slug' => 'properties', 'is_active' => true,
        ]));

        $this->get('/ar/properties')->assertOk()
            ->assertInertia(fn ($p) => $p->component('Site/Properties'));
    }

    public function test_generated_slugs_skip_reserved_route_names(): void
    {
        $page = Page::create(['title' => 'اتصل بنا', 'title_en' => 'Contact', 'is_active' => true]);

        $this->assertNotSame('contact', $page->slug);
        $this->assertSame('contact-2', $page->slug);
    }

    public function test_the_reserved_list_is_read_from_the_router(): void
    {
        $reserved = ReservedSlugs::all();

        // مش قايمة مكتوبة بالإيد — دول مسارات حقيقية في الموقع
        foreach (['properties', 'compounds', 'blog', 'about', 'contact', 'account'] as $segment) {
            $this->assertContains($segment, $reserved);
        }

        // متغيّر الراوت نفسه مش مقطع محجوز
        $this->assertNotContains('{slug}', $reserved);
    }

    public function test_the_admin_form_refuses_a_reserved_slug(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->actingAs($this->userWithRole('editor'))
            ->post('/admin/pages', ['title' => 'حاجة', 'slug' => 'blog', 'is_active' => true])
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseMissing('pages', ['slug' => 'blog']);
    }

    /* ---------------- السيو ---------------- */

    public function test_a_page_marked_hidden_asks_search_engines_not_to_index_it(): void
    {
        $this->page(['slug' => 'thank-you', 'is_indexable' => false]);

        $this->get('/ar/thank-you')->assertOk()
            ->assertInertia(fn ($p) => $p->where('meta.robots', 'noindex, follow'));

        // والصفحة العادية مالهاش الوسم أصلًا
        $this->page();
        $this->get('/ar/privacy-policy')->assertOk()
            ->assertInertia(fn ($p) => $p->missing('meta.robots'));
    }

    public function test_the_sitemap_lists_published_indexable_pages_only(): void
    {
        $this->page();
        $this->page(['slug' => 'terms', 'title' => 'الشروط']);
        $this->page(['slug' => 'draft-page', 'title' => 'مسوّدة', 'is_active' => false]);
        $this->page(['slug' => 'thank-you', 'title' => 'شكرًا', 'is_indexable' => false]);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('/ar/privacy-policy', $xml);
        $this->assertStringContainsString('/ar/terms', $xml);
        $this->assertStringNotContainsString('/ar/draft-page', $xml);
        $this->assertStringNotContainsString('/ar/thank-you', $xml);
    }

    /* ---------------- الصلاحيات ---------------- */

    public function test_the_screen_is_gated_on_managing_content(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->actingAs($this->userWithRole('editor'))->get('/admin/pages')->assertOk();

        // مدخل البيانات بيدير الكتالوج مش المحتوى
        $this->actingAs($this->userWithRole('data_entry'))->get('/admin/pages')->assertForbidden();
    }

    public function test_editing_a_page_from_the_dashboard_changes_the_public_page(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $page = $this->page();

        $this->actingAs($this->userWithRole('editor'))
            ->put("/admin/pages/{$page->id}", [
                'title' => 'سياسة الخصوصية',
                'slug' => 'privacy-policy',
                'body' => 'النص الجديد اللي الأدمن كتبه.',
                'is_active' => true,
            ])->assertRedirect();

        $this->get('/ar/privacy-policy')->assertOk()
            ->assertInertia(fn ($p) => $p->where('page.body', 'النص الجديد اللي الأدمن كتبه.'));
    }
}
