<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\Developers\Http\Controllers\DeveloperPageController;
use Modules\Locations\Http\Controllers\LocationPageController;
use Modules\Properties\Http\Controllers\PropertyPageController;

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

        /* ---------- العقارات ---------- */
        // ترتيب الراوتات مقصود: القسم (commercial/residential) قبل الـ slug،
        // و whereIn بيخلّي أي قيمة تانية تعدّي لراوت صفحة الوحدة بدل ما تتاكل هنا.
        Route::get('/properties', [PropertyPageController::class, 'index'])->name('properties');

        Route::get('/properties/{category}', [PropertyPageController::class, 'index'])
            ->whereIn('category', ['commercial', 'residential'])
            ->name('properties.category');

        Route::get('/properties/{slug}', [PropertyPageController::class, 'show'])->name('properties.show');

        /* ---------- المطوّرون ---------- */
        Route::get('/developers', [DeveloperPageController::class, 'index'])->name('developers');
        Route::get('/developers/{slug}', [DeveloperPageController::class, 'show'])->name('developers.show');

        /* ---------- المناطق ---------- */
        Route::get('/areas', [LocationPageController::class, 'index'])->name('areas');
        Route::get('/areas/{slug}', [LocationPageController::class, 'show'])->name('areas.show');

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

        Route::get('/compounds/{slug}', function (string $locale, string $slug) {
            $compound = \App\Support\Catalog::compound($locale, $slug);

            abort_if(! $compound, 404);

            $crumb = $locale === 'en' ? 'Compounds' : 'الكمبوندات';

            $summary = $compound['desc'] ?: ($locale === 'en'
                ? trim("{$compound['name']} by {$compound['developer']} in {$compound['area']} — from {$compound['starting']}, {$compound['down']} down, {$compound['years']}.")
                : trim("{$compound['name']} من {$compound['developer']} في {$compound['area']} — يبدأ من {$compound['starting']}، مقدم {$compound['down']}، {$compound['years']}."));

            return Inertia::render('Site/Compound', [
                'compound' => $compound,
                'units' => \App\Support\Catalog::compoundUnits($locale, $compound['id']),
                'meta' => \App\Support\Seo::page(
                    $locale,
                    $compound['name'],
                    $summary,
                    $compound['image'],
                    'article',
                    [
                        \App\Support\Seo::breadcrumb($locale, [
                            $crumb => '/compounds',
                            $compound['name'] => '/compounds/'.$compound['slug'],
                        ]),
                        \App\Support\Seo::project($compound + ['desc' => $summary], $locale),
                    ],
                ),
            ]);
        })->name('compounds.show');

        Route::get('/about', fn (string $locale) => Inertia::render('Site/About', [
            'milestones' => \App\Support\DemoContent::milestones($locale),
            'team'       => \App\Support\DemoContent::team($locale),
            'stats'      => \App\Support\Catalog::stats($locale),
            'developers' => \App\Support\Catalog::developers($locale),
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

        /* ---------- حساب العميل ---------- */
        // التسجيل العام بيطلّع دور customer بس — الوسطاء والشركات بيتعملوا من اللوحة
        Route::middleware('guest')->group(function () {
            Route::get('login', [\Modules\Core\Http\Controllers\AccountAuthController::class, 'showLogin'])
                ->name('account.login');
            Route::post('login', [\Modules\Core\Http\Controllers\AccountAuthController::class, 'login'])
                ->middleware('throttle:5,1')->name('account.login.store');

            Route::get('register', [\Modules\Core\Http\Controllers\AccountAuthController::class, 'showRegister'])
                ->name('account.register');
            Route::post('register', [\Modules\Core\Http\Controllers\AccountAuthController::class, 'register'])
                ->middleware('throttle:5,1')->name('account.register.store');
        });

        Route::middleware('auth')->group(function () {
            Route::post('logout', [\Modules\Core\Http\Controllers\AccountAuthController::class, 'logout'])
                ->name('account.logout');

            Route::get('account', [\Modules\Core\Http\Controllers\AccountController::class, 'index'])
                ->name('account.index');
            Route::get('account/favorites', [\Modules\Core\Http\Controllers\AccountController::class, 'favorites'])
                ->name('account.favorites');
            Route::get('account/requests', [\Modules\Core\Http\Controllers\AccountController::class, 'requests'])
                ->name('account.requests');
            Route::put('account', [\Modules\Core\Http\Controllers\AccountController::class, 'update'])
                ->name('account.update');

            Route::post('favorites/{property}', [\Modules\Core\Http\Controllers\FavoriteController::class, 'toggle'])
                ->whereNumber('property')->name('account.favorites.toggle');
        });

        // استقبال طلبات فورم "اتصل بنا" → موديول Leads
        Route::post('/leads', [\Modules\Leads\Http\Controllers\LeadController::class, 'store'])
            ->middleware('throttle:8,1')
            ->name('leads.store');


    });
