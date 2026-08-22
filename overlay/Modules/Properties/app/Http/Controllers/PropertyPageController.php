<?php

namespace Modules\Properties\Http\Controllers;

use App\Support\Catalog;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Properties\Models\Property;

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

    public function show(string $locale, string $slug): Response
    {
        $property = Catalog::property($locale, $slug);

        abort_if(! $property, 404);

        $crumb = $locale === 'en' ? 'Properties' : 'عقارات';

        // لو الأدمن مكتبش وصف، بنركّب جملة من البيانات نفسها — أحسن من ميتا فاضية
        $summary = $property['description'] ?: ($locale === 'en'
            ? trim("{$property['type']} {$property['size']}m² in {$property['area']} — {$property['beds']} bedrooms, {$property['baths']} bathrooms. {$property['price']}")
            : trim("{$property['type']} {$property['size']} م² في {$property['area']} — {$property['beds']} غرف نوم و{$property['baths']} حمام. {$property['price']}"));

        return Inertia::render('Site/Property', [
            'property' => $property,
            'related' => Catalog::relatedProperties($locale, $property),
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
