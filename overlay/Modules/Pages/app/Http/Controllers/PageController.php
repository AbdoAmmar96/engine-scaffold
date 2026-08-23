<?php

namespace Modules\Pages\Http\Controllers;

use App\Support\Seo;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Pages\Models\Page;

/**
 * عرض صفحة محتوى على `/{locale}/{slug}`.
 *
 * الراوت بتاعها آخر حاجة متسجّلة في مجموعة اللغة عشان أي مسار حقيقي يغلبها،
 * و`ReservedSlugs` بيمنع من الأساس إن صفحة تاخد اسم مسار موجود.
 */
class PageController extends Controller
{
    public function show(string $locale, string $slug): Response
    {
        $page = Page::published()->where('slug', $slug)->first();

        abort_if(! $page, 404);

        $title = $page->heading($locale);

        return Inertia::render('Site/Page', [
            'page' => $page->toPage($locale),
            'meta' => [
                ...Seo::page(
                    $locale,
                    $page->metaTitle($locale),
                    $page->metaDescription($locale),
                    null,
                    'article',
                    [Seo::breadcrumb($locale, [$title => '/'.$page->slug])],
                ),
                // متتفهرسش بس عدّي على اللينكات — صفحة شكر مثلًا
                ...($page->is_indexable ? [] : ['robots' => 'noindex, follow']),
            ],
        ]);
    }
}
