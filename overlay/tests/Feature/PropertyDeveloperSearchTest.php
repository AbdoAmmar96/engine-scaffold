<?php

namespace Tests\Feature;

use App\Support\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Compounds\Models\Compound;
use Modules\Developers\Models\Developer;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;
use Tests\TestCase;

/**
 * المطوّر على العقار.
 *
 * فورم البحث في الهيرو بيوعد بالبحث «بالمنطقة أو الكمبوند أو المطوّر»، والوعد ده
 * كان مكسور: الفلتر كان بيدوّر على العنوان والكود بس، والعقار مكانش له مطوّر
 * غير لو كان جوه كمبوند. الاختبارات دي بتقفل الاتنين.
 */
class PropertyDeveloperSearchTest extends TestCase
{
    use RefreshDatabase;

    private Developer $mostaqbal;

    private Developer $wadi;

    private Compound $compound;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mostaqbal = Developer::create(['name' => 'بناة المستقبل', 'name_en' => 'Future Builders']);
        $this->wadi = Developer::create(['name' => 'الوادي القابضة', 'name_en' => 'Wadi Holding']);

        $location = Location::create(['name' => 'التجمع الخامس', 'name_en' => 'Fifth Settlement']);

        $this->compound = Compound::create([
            'name' => 'النخيل هايتس',
            'name_en' => 'Palm Heights',
            'developer_id' => $this->wadi->id,
            'location_id' => $location->id,
        ]);
    }

    private function makeProperty(array $attributes = []): Property
    {
        return Property::create($attributes + [
            'title' => 'شقة 165م',
            'purpose' => 'sale',
            'type' => 'شقة',
            'is_active' => true,
        ]);
    }

    public function test_unit_inside_a_compound_inherits_the_compound_developer(): void
    {
        $property = $this->makeProperty(['compound_id' => $this->compound->id]);

        $this->assertSame($this->wadi->id, $property->resolvedDeveloper()?->id);
    }

    public function test_an_explicit_developer_beats_the_compound_one(): void
    {
        // إعادة بيع جوه مشروع: الوحدة بتاعة مطوّر تاني غير مطوّر الكمبوند
        $property = $this->makeProperty([
            'compound_id' => $this->compound->id,
            'developer_id' => $this->mostaqbal->id,
        ]);

        $this->assertSame($this->mostaqbal->id, $property->resolvedDeveloper()?->id);
    }

    public function test_a_standalone_unit_still_has_a_developer(): void
    {
        // الحالة اللي كانت مكسورة: مفيش كمبوند خالص
        $property = $this->makeProperty(['developer_id' => $this->mostaqbal->id]);

        $this->assertNull($property->compound);
        $this->assertSame($this->mostaqbal->id, $property->resolvedDeveloper()?->id);
        $this->assertSame('بناة المستقبل', $property->toCard('ar')['developer']);
    }

    public function test_a_unit_with_neither_reports_no_developer(): void
    {
        $this->assertNull($this->makeProperty()->resolvedDeveloper());
        $this->assertSame('', $this->makeProperty()->toCard('ar')['developer']);
    }

    public function test_search_finds_units_by_the_compound_developer(): void
    {
        $this->makeProperty(['compound_id' => $this->compound->id]);

        $this->assertCount(1, Catalog::properties('ar', null, ['q' => 'الوادي القابضة']));
    }

    public function test_search_finds_standalone_units_by_their_own_developer(): void
    {
        $this->makeProperty(['developer_id' => $this->mostaqbal->id]);

        $this->assertCount(1, Catalog::properties('ar', null, ['q' => 'بناة المستقبل']));
    }

    public function test_search_finds_units_by_compound_and_by_area(): void
    {
        $this->makeProperty(['compound_id' => $this->compound->id]);

        $this->assertCount(1, Catalog::properties('ar', null, ['q' => 'النخيل هايتس']));
        $this->assertCount(1, Catalog::properties('ar', null, ['q' => 'التجمع الخامس']));
    }

    public function test_search_matches_the_english_developer_name_too(): void
    {
        $this->makeProperty(['developer_id' => $this->mostaqbal->id]);

        $this->assertCount(1, Catalog::properties('en', null, ['q' => 'Future Builders']));
    }

    public function test_search_does_not_return_unrelated_units(): void
    {
        $this->makeProperty(['developer_id' => $this->mostaqbal->id]);

        $this->assertCount(0, Catalog::properties('ar', null, ['q' => 'الوادي القابضة']));
    }
}
