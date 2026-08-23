<?php

namespace Modules\Locations\Http\Controllers;

use App\Support\Catalog;
use App\Support\Seo;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

/** صفحات المناطق العامة: القائمة وصفحة كل منطقة بمشاريعها ووحداتها */
class LocationPageController extends Controller
{
    public function index(string $locale): Response
    {
        $areas = Catalog::allAreas($locale);
        $title = $locale === 'en' ? 'Areas' : 'المناطق';

        return Inertia::render('Site/Areas', [
            'areas' => $areas,
            'meta' => Seo::page(
                $locale,
                $title,
                $locale === 'en'
                    ? 'The areas we cover, what each one suits, and what is currently available in it.'
                    : 'المناطق التي نغطيها، ولمن تناسب كل منطقة، وما المتاح فيها حاليًا.',
                null,
                'website',
                [
                    Seo::breadcrumb($locale, [$title => '/areas']),
                    Seo::itemList($areas, $locale, '/areas'),
                ],
            ),
        ]);
    }

    public function show(string $locale, string $slug): Response
    {
        $data = Catalog::area($locale, $slug);

        abort_if(! $data, 404);

        $area = $data['area'];
        $crumb = $locale === 'en' ? 'Areas' : 'المناطق';

        $summary = $area['about'] ?: ($area['note'] ?: ($locale === 'en'
            ? "{$area['name']} — {$area['properties']} units and {$area['compounds']} projects currently listed."
            : "{$area['name']} — {$area['properties']} وحدة و{$area['compounds']} مشروع معروضة حاليًا."));

        return Inertia::render('Site/Area', [
            'area' => $area,
            'compounds' => $data['compounds'],
            'properties' => $data['properties'],
            'meta' => Seo::page(
                $locale,
                $area['name'],
                $summary,
                $area['cover'],
                'website',
                [
                    Seo::breadcrumb($locale, [
                        $crumb => '/areas',
                        $area['name'] => '/areas/'.$area['slug'],
                    ]),
                    Seo::place($area, $locale),
                ],
            ),
        ]);
    }
}
