<?php

namespace Modules\Properties\Http\Controllers;

use App\Support\Catalog;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Marketing\Support\AdSlot;
use Modules\Properties\Models\Property;
use Modules\Seo\Models\LandingPage;

/**
 * صفحات العقارات العامة. اتنقلت من routes/web.php لما الأقسام زادت —
 * /properties و /properties/commercial و /properties/{slug} بيشتغلوا بنفس المنطق.
 */
class PropertyPageController extends Controller
{
    /** القائمة — والقسم (سكني/تجاري) اختياري */
    public function index(Request $request, string $locale, ?string $category = null): Response
    {
        $filters = Catalog::filters($request);

        // القسم جاي من الرابط مش من الكويري، فبيغلب أي category متبعتة
        if ($category) {
            $filters['category'] = $category;
        }

        $properties = Catalog::properties($locale, null, $filters);
        $path = $category ? "/properties/{$category}" : '/properties';

        [$title, $description] = $this->copy($locale, $category);

        return Inertia::render('Site/Properties', [
            'properties' => $properties,
            'filters' => $filters,
            'category' => $category,
            'options' => Catalog::searchOptions($locale),
            'ads' => AdSlot::at('listing', $locale, 3),
            'meta' => Seo::page(
                $locale,
                $title,
                $description,
                $properties[0]['image'] ?? null,
                'website',
                [
                    Seo::breadcrumb($locale, [$title => $path]),
                    Seo::itemList($properties, $locale, $path),
                ],
            ),
        ]);
    }

    /**
     * الرابط ده بيخدم حاجتين: صفحة وحدة وصفحة هبوط برمجية.
     * الهبوط بيتشاف الأول عشان هو المجموعة المحدودة المعروفة، والتفرّد
     * بين الاتنين مضمون في SharedSlugSpace فمفيش رابط بيتاكل.
     */
    public function show(Request $request, string $locale, string $slug): Response
    {
        if ($landing = LandingPage::active()->with('location')->where('slug', $slug)->first()) {
            return $this->landing($request, $locale, $landing);
        }

        $property = Catalog::property($locale, $slug);

        abort_if(! $property, 404);

        // العدّاد ده هو اللي صاحب الوحدة بيشوفه في «وحداتي» — من غيره
        // الشاشة بتوعده بإحصائيات وتوريه أصفار على طول
        Property::recordView((int) $property['id']);

        // المسجّل قايمته بتمشي معاه بين الأجهزة؛ الزائر بيتخزّنله في المتصفح
        if ($user = $request->user()) {
            Property::recordVisit($user->id, (int) $property['id']);
        }

        $crumb = $locale === 'en' ? 'Properties' : 'عقارات';

        // لو الأدمن مكتبش وصف، بنركّب جملة من البيانات نفسها — أحسن من ميتا فاضية
        $summary = $property['description'] ?: ($locale === 'en'
            ? trim("{$property['type']} {$property['size']}m² in {$property['area']} — {$property['beds']} bedrooms, {$property['baths']} bathrooms. {$property['price']}")
            : trim("{$property['type']} {$property['size']} م² في {$property['area']} — {$property['beds']} غرف نوم و{$property['baths']} حمام. {$property['price']}"));

        return Inertia::render('Site/Property', [
            'property' => $property,
            'related' => Catalog::relatedProperties($locale, $property),
            'ads' => AdSlot::at('sidebar', $locale, 2),
            'meta' => Seo::page(
                $locale,
                $property['title'],
                $summary,
                $property['image'],
                'article',
                [
                    Seo::breadcrumb($locale, [
                        $crumb => '/properties',
                        $property['title'] => '/properties/'.$property['slug'],
                    ]),
                    Seo::residence($property + ['description' => $summary], $locale),
                ],
            ),
        ]);
    }

    /**
     * صفحة هبوط: نفس شاشة النتايج، بس عنوانها ونصها وميتاها من الصفحة
     * نفسها، وأبعادها (النوع/الغرض/المنطقة) مقفولة — الزائر يقدر يضيّق
     * أكتر بالسعر والمساحة، مش يغيّر موضوع الصفحة.
     */
    private function landing(Request $request, string $locale, LandingPage $landing): Response
    {
        $locked = $landing->filters();
        $filters = array_replace(Catalog::filters($request), $locked);

        $properties = Catalog::properties($locale, null, $filters);
        $path = '/properties/'.$landing->slug;
        $title = $landing->heading($locale);

        return Inertia::render('Site/Properties', [
            'properties' => $properties,
            'filters' => $filters,
            'category' => null,
            'options' => Catalog::searchOptions($locale),
            'ads' => AdSlot::at('listing', $locale, 3),
            'landing' => [
                'slug' => $landing->slug,
                'title' => $title,
                'intro' => $landing->intro($locale),
                'locked' => array_keys($locked),
                'related' => $this->relatedLandings($locale, $landing),
            ],
            'meta' => Seo::page(
                $locale,
                $landing->metaTitle($locale),
                $landing->metaDescription($locale),
                $properties[0]['image'] ?? null,
                'website',
                [
                    Seo::breadcrumb($locale, [
                        ($locale === 'en' ? 'Properties' : 'عقارات') => '/properties',
                        $title => $path,
                    ]),
                    Seo::itemList($properties, $locale, $path),
                ],
            ),
        ]);
    }

    /**
     * صفحات قريبة — نفس النوع في مناطق تانية، أو نفس المنطقة بأنواع تانية.
     * ده اللي بيربط الصفحات ببعض: من غيره كل صفحة هبوط جزيرة لوحدها
     * وجوجل بيوصلها من الخريطة بس.
     *
     * @return array<int, array{label: string, url: string, count: int}>
     */
    private function relatedLandings(string $locale, LandingPage $landing): array
    {
        $rows = LandingPage::active()
            ->with('location')
            ->where('id', '!=', $landing->id)
            ->where(fn ($q) => $q
                ->when($landing->type, fn ($s, $type) => $s->orWhere('type', $type))
                ->when($landing->location_id, fn ($s, $id) => $s->orWhere('location_id', $id)))
            ->orderByDesc('units_count')
            ->limit(8)
            ->get();

        return $rows->map(fn (LandingPage $p) => $p->toLink($locale))->all();
    }

    /** @return array{0:string, 1:string} */
    private function copy(string $locale, ?string $category): array
    {
        $en = $locale === 'en';

        return match ($category) {
            'commercial' => [
                $en ? 'Commercial properties' : 'عقارات تجارية',
                $en
                    ? 'Offices, retail units and clinics for sale and rent, with licence status and delivery dates stated.'
                    : 'مكاتب إدارية ومحلات وعيادات للبيع والإيجار، بحالة الترخيص وتاريخ التسليم موضّحين.',
            ],
            'residential' => [
                $en ? 'Residential properties' : 'عقارات سكنية',
                $en
                    ? 'Apartments, villas, duplexes and chalets for sale and rent across Egypt.'
                    : 'شقق وفيلات ودوبلكس وشاليهات للبيع والإيجار في كل المناطق.',
            ],
            default => [
                $en ? 'Properties for sale and rent' : 'عقارات للبيع والإيجار',
                $en
                    ? 'Browse verified units with documented prices, delivery dates and reference numbers.'
                    : 'تصفّح وحدات موثّقة بأسعار وتواريخ تسليم ورقم كود لكل وحدة.',
            ],
        };
    }

    /** الأقسام المسموح بيها في الرابط */
    public static function categories(): array
    {
        return array_keys(Property::CATEGORIES);
    }
}
