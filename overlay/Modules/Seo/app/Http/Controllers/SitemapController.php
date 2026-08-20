<?php

namespace Modules\Seo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Blog\Models\Post;
use Modules\Compounds\Models\Compound;
use Modules\Properties\Models\Property;

/**
 * خريطة الموقع — بتتبني من الداتابيز مع نسخة لكل لغة و hreflang بينهم.
 */
class SitemapController extends Controller
{
    private const LOCALES = ['ar', 'en'];

    public function index(): Response
    {
        $urls = [];

        foreach (['', '/properties', '/compounds', '/blog', '/about', '/contact'] as $path) {
            $urls[] = [
                'path' => $path,
                'changefreq' => $path === '' ? 'daily' : 'weekly',
                'priority' => $path === '' ? '1.0' : '0.8',
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
            'counts' => [
                'properties' => Property::where('is_active', true)->count(),
                'compounds' => Compound::where('is_active', true)->count(),
            ],
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
