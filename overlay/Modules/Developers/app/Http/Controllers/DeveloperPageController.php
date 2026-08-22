<?php

namespace Modules\Developers\Http\Controllers;

use App\Support\Catalog;
use App\Support\Seo;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

/** صفحات المطوّرين العامة: القائمة وصفحة كل مطوّر بمشاريعه */
class DeveloperPageController extends Controller
{
    public function index(string $locale): Response
    {
        $developers = Catalog::allDevelopers($locale);
        $title = $locale === 'en' ? 'Real-estate developers' : 'المطوّرون العقاريون';

        return Inertia::render('Site/Developers', [
            'developers' => $developers,
            'meta' => Seo::page(
                $locale,
                $title,
                $locale === 'en'
                    ? 'Developers we work with, their projects and the areas they build in.'
                    : 'المطوّرون اللي بنشتغل معاهم، مشاريعهم، والمناطق اللي بيبنوا فيها.',
                null,
                'website',
                [
                    Seo::breadcrumb($locale, [$title => '/developers']),
                    Seo::itemList($developers, $locale, '/developers'),
                ],
            ),
        ]);
    }

    public function show(string $locale, string $slug): Response
    {
        $data = Catalog::developer($locale, $slug);

        abort_if(! $data, 404);

        $developer = $data['developer'];
        $crumb = $locale === 'en' ? 'Developers' : 'المطوّرون';

        // من غير نبذة مكتوبة بنركّب جملة من الأرقام الحقيقية — مش ادعاءات
        $summary = $developer['about'] ?: ($locale === 'en'
            ? "{$developer['name']} — {$developer['compounds']} projects and {$developer['units']} units listed across {$developer['areas']} areas."
            : "{$developer['name']} — {$developer['compounds']} مشروع و{$developer['units']} وحدة معروضة في {$developer['areas']} منطقة.");

        return Inertia::render('Site/Developer', [
            'developer' => $developer,
            'compounds' => $data['compounds'],
            'units' => $data['units'],
            'meta' => Seo::page(
                $locale,
                $developer['name'],
                $summary,
                $developer['cover'],
                'website',
                [
                    Seo::breadcrumb($locale, [
                        $crumb => '/developers',
                        $developer['name'] => '/developers/'.$developer['slug'],
                    ]),
                    Seo::organizationProfile($developer, $locale),
                ],
            ),
        ]);
    }
}
