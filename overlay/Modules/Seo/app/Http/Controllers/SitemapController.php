<?php

namespace Modules\Seo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Blog\Models\Post;
use Modules\Compounds\Models\Compound;
use Modules\Developers\Models\Developer;
use Modules\Locations\Models\Location;
use Modules\Pages\Models\Page;
use Modules\Properties\Models\Property;
use Modules\Seo\Models\LandingPage;

/**
 * خريطة الموقع — بتتبني من الداتابيز مع نسخة لكل لغة و hreflang بينهم.
 */
class SitemapController extends Controller
{
    private const LOCALES = ['ar', 'en'];

    public function index(): Response
    {
        $urls = [];

        $static = [
            '', '/properties', '/properties/residential', '/properties/commercial',
            '/compounds', '/developers', '/areas', '/blog', '/about', '/contact',
        ];

        foreach ($static as $path) {
            $urls[] = [
                'path' => $path,
                'changefreq' => $path === '' ? 'daily' : 'weekly',
                'priority' => $path === '' ? '1.0' : '0.8',
            ];
        }

        // صفحات المحتوى — اللي متعلّم عليها متتفهرسش مبتدخلش الخريطة أصلًا
        foreach (Page::published()->where('is_indexable', true)->orderBy('sort')->get() as $page) {
            $urls[] = [
                'path' => '/'.$page->slug,
                'lastmod' => $page->updated_at?->toAtomString(),
                'changefreq' => 'yearly',
                'priority' => '0.4',
            ];
        }

        foreach (Developer::where('is_active', true)->whereNotNull('slug')->get() as $developer) {
            $urls[] = [
                'path' => '/developers/'.$developer->slug,
                'lastmod' => $developer->updated_at?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        foreach (Location::where('is_active', true)->whereNotNull('slug')->get() as $area) {
            $urls[] = [
                'path' => '/areas/'.$area->slug,
                'lastmod' => $area->updated_at?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        // صفحات الهبوط البرمجية — الأولوية على قد عدد وحداتها:
        // صفحة فيها ٤٠ وحدة تستاهل زحف أكتر من صفحة فيها وحدة
        foreach (LandingPage::active()->orderByDesc('units_count')->get() as $landing) {
            $urls[] = [
                'path' => '/properties/'.$landing->slug,
                'lastmod' => $landing->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => $landing->units_count >= 10 ? '0.8' : '0.6',
            ];
        }

        // صفحات العرض — الوحدات بتتغيّر أكتر من المشاريع فأولويتها أعلى
        foreach (Property::published()->whereNotNull('slug')->get() as $property) {
            $urls[] = [
                'path' => '/properties/'.$property->slug,
                'lastmod' => $property->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        foreach (Compound::where('is_active', true)->whereNotNull('slug')->get() as $compound) {
            $urls[] = [
                'path' => '/compounds/'.$compound->slug,
                'lastmod' => $compound->updated_at?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        foreach (Post::published()->get() as $post) {
            $urls[] = [
                'path' => '/blog/'.$post->slug,
                'lastmod' => $post->updated_at?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        $xml = view('seo::sitemap', [
            'urls' => $urls,
            'locales' => self::LOCALES,
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /storage/media',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
