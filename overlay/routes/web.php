<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// الجذر → العربية (اللغة الافتراضية)
Route::redirect('/', '/ar');

// كل صفحات الموقع العام تحت بادئة اللغة: /ar/... و /en/...
Route::prefix('{locale}')
    ->whereIn('locale', ['ar', 'en'])
    ->middleware('locale')
    ->group(function () {

        Route::get('/', fn (string $locale) => Inertia::render('Site/Home', [
            'latestProperties' => \App\Support\Catalog::properties($locale, 6),
            'latestCompounds'  => \App\Support\Catalog::compounds($locale, 3),
            'areas'            => \App\Support\Catalog::areas($locale, 3),
            'searchOptions'    => \App\Support\Catalog::searchOptions($locale),
            'meta'             => \App\Support\Seo::page($locale, ''),
        ]))->name('home');

        Route::get('/properties', function (Request $request, string $locale) {
            $filters = \App\Support\Catalog::filters($request);

            $properties = \App\Support\Catalog::properties($locale, null, $filters);
            $title = $locale === 'en' ? 'Properties for sale and rent' : 'عقارات للبيع والإيجار';

            return Inertia::render('Site/Properties', [
                'properties' => $properties,
                'filters' => $filters,
                'options' => \App\Support\Catalog::searchOptions($locale),
                'meta' => \App\Support\Seo::page(
                    $locale,
                    $title,
                    $locale === 'en'
                        ? 'Browse verified units with documented prices, delivery dates and reference numbers.'
                        : 'تصفّح وحدات موثّقة بأسعار وتواريخ تسليم ورقم كود لكل وحدة.',
                    $properties[0]['image'] ?? null,
                    'website',
                    [
                        \App\Support\Seo::breadcrumb($locale, [$title => '/properties']),
                        \App\Support\Seo::itemList($properties, $locale, '/properties'),
                    ],
                ),
            ]);
        })->name('properties');

        Route::get('/compounds', function (Request $request, string $locale) {
            $filters = \App\Support\Catalog::filters($request);

            $compounds = \App\Support\Catalog::compounds($locale, null, $filters);
            $title = $locale === 'en' ? 'Compounds' : 'الكمبوندات';

            return Inertia::render('Site/Compounds', [
                'compounds' => $compounds,
                'filters' => $filters,
                'options' => \App\Support\Catalog::searchOptions($locale),
                'meta' => \App\Support\Seo::page(
                    $locale,
                    $title,
                    $locale === 'en'
                        ? 'Residential and coastal projects with developer payment plans and contract-documented delivery dates.'
                        : 'مشاريع سكنية وساحلية بأنظمة سداد من المطوّر وتواريخ تسليم موثّقة في العقد.',
                    $compounds[0]['image'] ?? null,
                    'website',
                    [
                        \App\Support\Seo::breadcrumb($locale, [$title => '/compounds']),
                        \App\Support\Seo::itemList($compounds, $locale, '/compounds'),
                    ],
                ),
            ]);
        })->name('compounds');

        Route::get('/about', fn (string $locale) => Inertia::render('Site/About', [
            'milestones' => \App\Support\DemoContent::milestones($locale),
            'team'       => \App\Support\DemoContent::team($locale),
            'stats'      => \App\Support\Catalog::stats($locale),
            'meta'       => \App\Support\Seo::page(
                $locale,
                $locale === 'en' ? 'About us' : 'من نحن',
                $locale === 'en'
                    ? 'Who we are, how we work, and why we say no when a unit does not fit.'
                    : 'مين إحنا، وبنشتغل إزاي، وليه بنقول «لأ» لما الوحدة مش مناسبة.',
                null,
                'website',
                [\App\Support\Seo::breadcrumb($locale, [($locale === 'en' ? 'About us' : 'من نحن') => '/about'])],
            ),
        ]))->name('about');

        Route::get('/contact', fn (string $locale) => Inertia::render('Site/Contact', [
            'options' => \App\Support\DemoContent::contactOptions($locale),
            'meta'    => \App\Support\Seo::page(
                $locale,
                $locale === 'en' ? 'Contact us' : 'اتصل بنا',
                $locale === 'en'
                    ? 'Tell us your budget and area and we will shortlist what actually fits.'
                    : 'قوللنا ميزانيتك والمنطقة وهنرشّح لك اللي يناسبك فعلًا.',
                null,
                'website',
                [\App\Support\Seo::breadcrumb($locale, [($locale === 'en' ? 'Contact us' : 'اتصل بنا') => '/contact'])],
            ),
        ]))->name('contact');

        Route::get('/blog', fn (string $locale) => Inertia::render('Site/Blog', [
            'posts' => \App\Support\Catalog::posts($locale),
            'meta'  => \App\Support\Seo::page(
                $locale,
                $locale === 'en' ? 'Real-estate blog' : 'المدونة العقارية',
                $locale === 'en'
                    ? 'Analysis and practical guides on the Egyptian property market.'
                    : 'تحليلات ودلائل عملية عن السوق العقاري المصري.',
                null,
                'website',
                [\App\Support\Seo::breadcrumb($locale, [($locale === 'en' ? 'Blog' : 'المدونة') => '/blog'])],
            ),
        ]))->name('blog');

        Route::get('/blog/{slug}', function (string $locale, string $slug) {
            $post = \App\Support\Catalog::post($locale, $slug);

            abort_if(! $post, 404);

            $more = array_filter(
                \App\Support\Catalog::posts($locale, 4),
                fn ($p) => $p['slug'] !== $slug,
            );

            return Inertia::render('Site/Post', [
                'post' => $post,
                'more' => array_slice(array_values($more), 0, 3),
                'meta' => \App\Support\Seo::page(
                    $locale,
                    $post['title'],
                    $post['excerpt'],
                    $post['image'],
                    'article',
                    [
                        \App\Support\Seo::breadcrumb($locale, [
                            ($locale === 'en' ? 'Blog' : 'المدونة') => '/blog',
                            $post['title'] => '/blog/'.$post['slug'],
                        ]),
                        \App\Support\Seo::article($post, $locale),
                    ],
                ),
            ]);
        })->name('blog.show');

        // استقبال طلبات فورم "اتصل بنا" → موديول Leads
        Route::post('/leads', [\Modules\Leads\Http\Controllers\LeadController::class, 'store'])
            ->middleware('throttle:8,1')
            ->name('leads.store');


    });
