<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Compounds\Models\Compound;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;
use Modules\Seo\Models\LandingPage;
use Tests\TestCase;

/**
 * صفحات الهبوط البرمجية.
 *
 * الخطر الأساسي هنا مش إن الصفحة ماتتعملش — إنها تتعمل فاضية أو تتكرّر
 * أو تاكل رابط وحدة. فالاختبارات مركّزة على الحاجات دي.
 */
class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    private Location $cairo;

    private Location $alex;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cairo = Location::create(['name' => 'القاهرة الجديدة', 'name_en' => 'New Cairo']);
        $this->alex = Location::create(['name' => 'الإسكندرية', 'name_en' => 'Alexandria']);

        $this->unit('A1', ['type' => 'شقة', 'purpose' => 'sale', 'location_id' => $this->cairo->id]);
        $this->unit('A2', ['type' => 'شقة', 'purpose' => 'sale', 'location_id' => $this->cairo->id]);
        $this->unit('V1', ['type' => 'فيلا', 'purpose' => 'rent', 'location_id' => $this->alex->id]);
    }

    private function unit(string $ref, array $extra = []): Property
    {
        return Property::create(array_merge([
            'title' => "وحدة {$ref}",
            'ref' => $ref,
            'purpose' => 'sale',
            'type' => 'شقة',
            'status' => 'published',
            'is_active' => true,
        ], $extra));
    }

    private function generate(array $options = []): void
    {
        $this->artisan('seo:landing-pages', $options)->assertSuccessful();
    }

    public function test_it_builds_slugs_from_the_combination(): void
    {
        $this->generate();

        $this->assertDatabaseHas('seo_landing_pages', ['slug' => 'apartments-for-sale']);
        $this->assertDatabaseHas('seo_landing_pages', ['slug' => 'apartments-for-sale-in-new-cairo', 'units_count' => 2]);
        $this->assertDatabaseHas('seo_landing_pages', ['slug' => 'properties-in-alexandria', 'units_count' => 1]);
    }

    public function test_it_only_builds_combinations_that_have_units(): void
    {
        $this->generate();

        // مفيش فيلات في القاهرة الجديدة ولا شقق في الإسكندرية
        $this->assertDatabaseMissing('seo_landing_pages', ['slug' => 'villas-for-rent-in-new-cairo']);
        $this->assertDatabaseMissing('seo_landing_pages', ['slug' => 'apartments-for-sale-in-alexandria']);
    }

    public function test_running_it_twice_updates_instead_of_duplicating(): void
    {
        $this->generate();
        $before = LandingPage::count();

        $this->unit('A3', ['type' => 'شقة', 'purpose' => 'sale', 'location_id' => $this->cairo->id]);
        $this->generate();

        $this->assertSame($before, LandingPage::count());
        $this->assertSame(3, LandingPage::where('slug', 'apartments-for-sale-in-new-cairo')->value('units_count'));
    }

    public function test_a_page_that_runs_out_of_units_is_deactivated_not_deleted(): void
    {
        $this->generate();

        Property::where('ref', 'V1')->update(['status' => 'sold']);
        $this->generate();

        $page = LandingPage::where('slug', 'villas-for-rent')->first();

        $this->assertNotNull($page);
        $this->assertFalse($page->is_active);
        $this->assertSame(0, $page->units_count);
    }

    public function test_prune_deletes_only_pages_nobody_wrote_copy_for(): void
    {
        $this->generate();

        LandingPage::where('slug', 'villas-for-rent')->update(['h1' => 'فيلات للإيجار في الساحل']);
        Property::where('ref', 'V1')->update(['status' => 'sold']);

        $this->generate(['--prune' => true]);

        // اللي مكتوب فيه نص بيتوقف بس — نص المحرّر مايضيعش
        $this->assertDatabaseHas('seo_landing_pages', ['slug' => 'villas-for-rent', 'is_active' => false]);
        $this->assertDatabaseMissing('seo_landing_pages', ['slug' => 'villas-for-rent-in-alexandria']);
    }

    public function test_the_page_shows_only_its_own_units(): void
    {
        $this->generate();

        $response = $this->get('/ar/properties/apartments-for-sale-in-new-cairo');

        $response->assertOk();
        $refs = array_column($response->viewData('page')['props']['properties'], 'ref');

        sort($refs);
        $this->assertSame(['A1', 'A2'], $refs);
    }

    public function test_the_query_string_cannot_change_what_the_page_is_about(): void
    {
        $this->generate();

        // ?purpose=rent&type=فيلا على صفحة «شقق للبيع» — الصفحة بتفضل هي هي
        $response = $this->get('/ar/properties/apartments-for-sale-in-new-cairo?purpose=rent&type='.urlencode('فيلا').'&location='.urlencode('الإسكندرية'));

        $response->assertOk();
        $props = $response->viewData('page')['props'];

        $this->assertSame(['A1', 'A2'], collect($props['properties'])->pluck('ref')->sort()->values()->all());
        $this->assertSame('sale', $props['filters']['purpose']);
        $this->assertSame('شقة', $props['filters']['type']);
    }

    public function test_visitors_can_still_narrow_the_page_further(): void
    {
        Property::where('ref', 'A1')->update(['price_amount' => 1_000_000]);
        Property::where('ref', 'A2')->update(['price_amount' => 9_000_000]);

        $this->generate();

        $response = $this->get('/ar/properties/apartments-for-sale-in-new-cairo?price_max=2000000');

        $response->assertOk();
        $this->assertSame(['A1'], array_column($response->viewData('page')['props']['properties'], 'ref'));
    }

    public function test_a_deactivated_page_is_gone_from_the_site(): void
    {
        $this->generate();
        LandingPage::where('slug', 'apartments-for-sale')->update(['is_active' => false]);

        $this->get('/ar/properties/apartments-for-sale')->assertNotFound();
    }

    public function test_admin_copy_wins_over_the_generated_copy(): void
    {
        $this->generate();

        LandingPage::where('slug', 'apartments-for-sale')->update([
            'h1' => 'شقق للبيع بالتقسيط',
            'meta_description' => 'وصف مكتوب بالإيد.',
        ]);

        $response = $this->get('/ar/properties/apartments-for-sale');
        $props = $response->viewData('page')['props'];

        $this->assertSame('شقق للبيع بالتقسيط', $props['landing']['title']);
        $this->assertStringContainsString('شقق للبيع بالتقسيط', $props['meta']['title']);
        $this->assertSame('وصف مكتوب بالإيد.', $props['meta']['description']);
    }

    public function test_generated_copy_differs_per_language(): void
    {
        $this->generate();

        $ar = $this->get('/ar/properties/apartments-for-sale-in-new-cairo')->viewData('page')['props'];
        $en = $this->get('/en/properties/apartments-for-sale-in-new-cairo')->viewData('page')['props'];

        $this->assertSame('شقق للبيع في القاهرة الجديدة', $ar['landing']['title']);
        $this->assertSame('Apartments for sale in New Cairo', $en['landing']['title']);

        // نفس المسار للغتين — hreflang بيتبني عليه
        $this->assertSame(url('/en/properties/apartments-for-sale-in-new-cairo'), $ar['meta']['alternates']['en']);
    }

    public function test_a_unit_cannot_take_a_landing_page_url(): void
    {
        $this->generate();

        $unit = Property::create([
            'title' => 'Apartments for sale',
            'title_en' => 'Apartments for sale',
            'purpose' => 'sale',
            'type' => 'شقة',
            'status' => 'published',
        ]);

        $this->assertNotSame('apartments-for-sale', $unit->slug);
        $this->get('/ar/properties/apartments-for-sale')->assertOk();
    }

    public function test_a_landing_page_cannot_take_a_unit_url(): void
    {
        $this->unit('CLASH', ['title' => 'شقق للبيع', 'title_en' => 'Apartments for sale', 'type' => 'شقة', 'purpose' => 'sale']);

        $this->assertSame('apartments-for-sale', Property::where('ref', 'CLASH')->value('slug'));

        $this->generate();

        $page = LandingPage::where('type', 'شقة')->whereNull('location_id')->first();

        $this->assertNotSame('apartments-for-sale', $page->slug);
    }

    public function test_units_inherit_their_compound_area(): void
    {
        $compound = Compound::create(['name' => 'مشروع النخيل', 'location_id' => $this->alex->id]);
        $this->unit('C1', ['type' => 'شاليه', 'purpose' => 'sale', 'compound_id' => $compound->id]);

        $this->generate();

        // الوحدة مالهاش location_id بس كارتها بيقول الإسكندرية — الصفحة لازم تعدّها
        $this->assertDatabaseHas('seo_landing_pages', ['slug' => 'chalets-for-sale-in-alexandria', 'units_count' => 1]);
        $this->assertSame(2, LandingPage::where('slug', 'properties-in-alexandria')->value('units_count'));

        $response = $this->get('/ar/properties/chalets-for-sale-in-alexandria');
        $this->assertSame(['C1'], array_column($response->viewData('page')['props']['properties'], 'ref'));
    }

    public function test_the_sitemap_lists_active_pages_only(): void
    {
        $this->generate();
        LandingPage::where('slug', 'villas-for-rent')->update(['is_active' => false]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee('/ar/properties/apartments-for-sale-in-new-cairo', false);
        $response->assertDontSee('/ar/properties/villas-for-rent<', false);
    }
}
