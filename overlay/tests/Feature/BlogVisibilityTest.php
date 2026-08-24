<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Blog\Models\Post;
use Modules\Core\Database\Seeders\MenuSeeder;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Core\Services\SettingsService;
use Tests\TestCase;

/**
 * قفل قسم المدونة من الداشبورد.
 *
 * القسم المقفول لازم يختفي من **أربع** حتت مع بعض. الاختبارات دي بتقفل
 * الأربعة، لأن نسيان واحدة بيسيب القسم موجود بشكل تاني ومحدش بياخد باله:
 * جوجل بيفهرس رابط بيرجّع 404، أو لينك في القايمة بيودّي على 404،
 * أو الأدمن بينشر مقال ويستنى ظهوره وهو مقفول من الأساس.
 */
class BlogVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function article(): Post
    {
        return Post::create([
            'slug' => 'market-2026',
            'title' => 'السوق العقاري 2026',
            'excerpt' => 'مقتطف.',
            'body' => 'نص المقال.',
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    private function enable(string $value): void
    {
        app(SettingsService::class)->setMany('general', ['blog_enabled' => $value]);
    }

    /* ---------------- الافتراضي ---------------- */

    public function test_the_blog_is_closed_when_nothing_was_chosen(): void
    {
        // مفيش صف إعداد أصلًا — التثبيت الجديد والقديم الاتنين يقفلوا القسم
        $this->assertFalse(Features::enabled('blog'));
    }

    public function test_a_closed_blog_is_a_404_not_an_empty_page(): void
    {
        $this->article();

        $this->get('/ar/blog')->assertNotFound();
        $this->get('/ar/blog/market-2026')->assertNotFound();
        $this->get('/en/blog')->assertNotFound();
    }

    public function test_opening_it_brings_the_page_back(): void
    {
        $this->article();
        $this->enable('1');

        $this->get('/ar/blog')->assertOk()
            ->assertInertia(fn ($p) => $p->component('Site/Blog')->has('posts', 1));

        $this->get('/ar/blog/market-2026')->assertOk()
            ->assertInertia(fn ($p) => $p->component('Site/Post'));
    }

    public function test_closing_it_keeps_the_posts(): void
    {
        $this->article();
        $this->enable('1');
        $this->enable('0');

        // القفل إخفاء مش حذف — الأدمن بيجهّز محتواه والقسم مقفول
        $this->assertSame(1, Post::count());
        $this->get('/ar/blog')->assertNotFound();
    }

    /* ---------------- القائمة ---------------- */

    public function test_the_menu_drops_the_link_while_it_is_closed(): void
    {
        $this->seed(MenuSeeder::class);

        $labels = fn () => collect($this->get('/ar')->assertOk()->viewData('page')['props']['menu']['header'])
            ->pluck('label')->all();

        $this->assertNotContains('المدونة', $labels());

        $this->enable('1');
        $this->assertContains('المدونة', $labels());
    }

    public function test_a_closed_section_is_dropped_from_a_dropdown_too(): void
    {
        // «خدماتنا» فيها أبناء — الفلترة لازم توصل للمستوى التاني كمان
        $this->seed(MenuSeeder::class);

        $header = $this->get('/ar')->assertOk()->viewData('page')['props']['menu']['header'];
        $services = collect($header)->firstWhere('label', 'خدماتنا');

        $this->assertNotNull($services);
        $this->assertNotContains('المدونة', collect($services['children'])->pluck('label')->all());
    }

    /* ---------------- خريطة الموقع ---------------- */

    public function test_the_sitemap_hides_the_section_and_its_posts(): void
    {
        $this->article();

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringNotContainsString('/ar/blog', $xml);
        $this->assertStringNotContainsString('/blog/market-2026', $xml);

        $this->enable('1');
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('/ar/blog', $xml);
        $this->assertStringContainsString('/blog/market-2026', $xml);
    }

    /* ---------------- اللوحة ---------------- */

    public function test_the_switch_shows_up_in_the_settings_screen(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->enable('0');

        $user = User::create(['name' => 'مدير', 'email' => 'admin@test.local', 'password' => 'password123']);
        $user->syncRoles(['super_admin']);

        $this->actingAs($user)->get('/admin/settings/general')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->where('types.blog_enabled', 'toggle')
                ->where('values.blog_enabled', '0'));
    }

    public function test_the_switch_refuses_a_value_that_is_not_a_switch(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->enable('0');

        $user = User::create(['name' => 'مدير', 'email' => 'admin2@test.local', 'password' => 'password123']);
        $user->syncRoles(['super_admin']);

        $this->actingAs($user)
            ->put('/admin/settings/general', ['values' => ['blog_enabled' => 'yes']])
            ->assertSessionHasErrors('values.blog_enabled');

        $this->assertFalse(Features::enabled('blog'));
    }

    public function test_managing_posts_stays_open_while_the_section_is_closed(): void
    {
        // الأدمن لازم يقدر يجهّز المحتوى قبل ما يفتح القسم
        $this->seed(RolePermissionSeeder::class);

        $user = User::create(['name' => 'محرّر', 'email' => 'editor@test.local', 'password' => 'password123']);
        $user->syncRoles(['editor']);

        $this->actingAs($user)->get('/admin/posts')->assertOk();
    }
}
