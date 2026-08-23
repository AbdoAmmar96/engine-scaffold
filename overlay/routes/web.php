<?php

use App\Support\Catalog;
use App\Support\DemoContent;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\Core\Http\Controllers\AccountAuthController;
use Modules\Core\Http\Controllers\AccountController;
use Modules\Core\Http\Controllers\FavoriteController;
use Modules\Core\Http\Controllers\PasswordResetController;
use Modules\Developers\Http\Controllers\DeveloperPageController;
use Modules\Leads\Http\Controllers\LeadController;
use Modules\Locations\Http\Controllers\LocationPageController;
use Modules\Marketing\Http\Controllers\AdClickController;
use Modules\Marketing\Support\AdSlot;
use Modules\Pages\Http\Controllers\AboutController;
use Modules\Pages\Http\Controllers\PageController;
use Modules\Properties\Http\Controllers\AccountPropertyController;
use Modules\Properties\Http\Controllers\AddPropertyController;
use Modules\Properties\Http\Controllers\PropertyPageController;
use Modules\Properties\Http\Controllers\RecentlyViewedController;
use Modules\Properties\Http\Controllers\SavedSearchController;
use Modules\Reviews\Http\Controllers\AccountReviewController;

// الجذر → العربية (اللغة الافتراضية)
Route::redirect('/', '/ar');

// كل صفحات الموقع العام تحت بادئة اللغة: /ar/... و /en/...
Route::prefix('{locale}')
    ->whereIn('locale', ['ar', 'en'])
    ->middleware('locale')
    ->group(function () {

        Route::get('/', fn (string $locale) => Inertia::render('Site/Home', [
            'latestProperties' => Catalog::properties($locale, 6),
            'latestCompounds' => Catalog::compounds($locale, 3),
            'areas' => Catalog::areas($locale, 3),
            'searchOptions' => Catalog::searchOptions($locale),
            'ads' => AdSlot::at('hero', $locale, 3),
            'recentlyViewed' => Catalog::recentlyViewed($locale),
            'reviews' => Catalog::reviews($locale, 6),
            'meta' => Seo::page($locale, ''),
        ]))->name('home');

        /* ---------- العقارات ---------- */
        // ترتيب الراوتات مقصود: القسم (commercial/residential) قبل الـ slug،
        // و whereIn بيخلّي أي قيمة تانية تعدّي لراوت صفحة الوحدة بدل ما تتاكل هنا.
        Route::get('/properties', [PropertyPageController::class, 'index'])->name('properties');

        Route::get('/properties/{category}', [PropertyPageController::class, 'index'])
            ->whereIn('category', ['commercial', 'residential'])
            ->name('properties.category');

        Route::get('/properties/{slug}', [PropertyPageController::class, 'show'])->name('properties.show');

        /* ---------- أضف عقارك ---------- */
        // فوق راوت /properties/{slug} مش تحته: ده مسار ثابت مالوش علاقة بالوحدات
        Route::get('/add-property', [AddPropertyController::class, 'create'])->name('add-property');
        Route::post('/add-property', [AddPropertyController::class, 'store'])
            ->middleware('throttle:5,60')
            ->name('add-property.store');

        // كروت «شوهدت مؤخرًا» — المتصفح بيبعت الأرقام والسيرفر بيرجّع الكروت
        Route::get('/recently-viewed', RecentlyViewedController::class)->name('recently-viewed');

        /* ---------- تتبّع الإعلانات ---------- */
        // ريدايركت مش جافاسكربت: الضغطة بتتحسب حتى لو الـ JS واقع
        Route::get('/ads/{ad}', AdClickController::class)->whereNumber('ad')->name('ads.click');

        /* ---------- المطوّرون ---------- */
        Route::get('/developers', [DeveloperPageController::class, 'index'])->name('developers');
        Route::get('/developers/{slug}', [DeveloperPageController::class, 'show'])->name('developers.show');

        /* ---------- المناطق ---------- */
        Route::get('/areas', [LocationPageController::class, 'index'])->name('areas');
        Route::get('/areas/{slug}', [LocationPageController::class, 'show'])->name('areas.show');

        Route::get('/compounds', function (Request $request, string $locale) {
            $filters = Catalog::filters($request);

            $compounds = Catalog::compounds($locale, null, $filters);
            $title = $locale === 'en' ? 'Compounds' : 'الكمبوندات';

            return Inertia::render('Site/Compounds', [
                'compounds' => $compounds,
                'filters' => $filters,
                'options' => Catalog::searchOptions($locale),
                'meta' => Seo::page(
                    $locale,
                    $title,
                    $locale === 'en'
                        ? 'Residential and coastal projects with developer payment plans and contract-documented delivery dates.'
                        : 'مشاريع سكنية وساحلية بأنظمة سداد من المطوّر وتواريخ تسليم موثّقة في العقد.',
                    $compounds[0]['image'] ?? null,
                    'website',
                    [
                        Seo::breadcrumb($locale, [$title => '/compounds']),
                        Seo::itemList($compounds, $locale, '/compounds'),
                    ],
                ),
            ]);
        })->name('compounds');

        Route::get('/compounds/{slug}', function (string $locale, string $slug) {
            $compound = Catalog::compound($locale, $slug);

            abort_if(! $compound, 404);

            $crumb = $locale === 'en' ? 'Compounds' : 'الكمبوندات';

            $summary = $compound['desc'] ?: ($locale === 'en'
                ? trim("{$compound['name']} by {$compound['developer']} in {$compound['area']} — from {$compound['starting']}, {$compound['down']} down, {$compound['years']}.")
                : trim("{$compound['name']} من {$compound['developer']} في {$compound['area']} — يبدأ من {$compound['starting']}، مقدم {$compound['down']}، {$compound['years']}."));

            return Inertia::render('Site/Compound', [
                'compound' => $compound,
                'units' => Catalog::compoundUnits($locale, $compound['id']),
                'meta' => Seo::page(
                    $locale,
                    $compound['name'],
                    $summary,
                    $compound['image'],
                    'article',
                    [
                        Seo::breadcrumb($locale, [
                            $crumb => '/compounds',
                            $compound['name'] => '/compounds/'.$compound['slug'],
                        ]),
                        Seo::project($compound + ['desc' => $summary], $locale),
                    ],
                ),
            ]);
        })->name('compounds.show');

        // «من نحن» بقت من الإعدادات — الكنترولر بيقرا مجموعة `about`
        Route::get('/about', [AboutController::class, 'show'])->name('about');

        Route::get('/contact', fn (string $locale) => Inertia::render('Site/Contact', [
            'options' => DemoContent::contactOptions($locale),
            'meta' => Seo::page(
                $locale,
                $locale === 'en' ? 'Contact us' : 'اتصل بنا',
                $locale === 'en'
                    ? 'Tell us your budget and area and we will shortlist what actually fits.'
                    : 'أخبرنا بميزانيتك والمنطقة وسنرشّح لك ما يناسبك فعلًا.',
                null,
                'website',
                [Seo::breadcrumb($locale, [($locale === 'en' ? 'Contact us' : 'اتصل بنا') => '/contact'])],
            ),
        ]))->name('contact');

        Route::get('/blog', fn (string $locale) => Inertia::render('Site/Blog', [
            'posts' => Catalog::posts($locale),
            'meta' => Seo::page(
                $locale,
                $locale === 'en' ? 'Real-estate blog' : 'المدونة العقارية',
                $locale === 'en'
                    ? 'Analysis and practical guides on the Egyptian property market.'
                    : 'تحليلات وأدلة عملية عن السوق العقاري المصري.',
                null,
                'website',
                [Seo::breadcrumb($locale, [($locale === 'en' ? 'Blog' : 'المدونة') => '/blog'])],
            ),
        ]))->name('blog');

        Route::get('/blog/{slug}', function (string $locale, string $slug) {
            $post = Catalog::post($locale, $slug);

            abort_if(! $post, 404);

            $more = array_filter(
                Catalog::posts($locale, 4),
                fn ($p) => $p['slug'] !== $slug,
            );

            return Inertia::render('Site/Post', [
                'post' => $post,
                'more' => array_slice(array_values($more), 0, 3),
                'meta' => Seo::page(
                    $locale,
                    $post['title'],
                    $post['excerpt'],
                    $post['image'],
                    'article',
                    [
                        Seo::breadcrumb($locale, [
                            ($locale === 'en' ? 'Blog' : 'المدونة') => '/blog',
                            $post['title'] => '/blog/'.$post['slug'],
                        ]),
                        Seo::article($post, $locale),
                    ],
                ),
            ]);
        })->name('blog.show');

        /* ---------- حساب العميل ---------- */
        // التسجيل العام بيطلّع دور customer بس — الوسطاء والشركات بيتعملوا من اللوحة
        Route::middleware('guest')->group(function () {
            Route::get('login', [AccountAuthController::class, 'showLogin'])
                ->name('account.login');
            Route::post('login', [AccountAuthController::class, 'login'])
                ->middleware('throttle:5,1')->name('account.login.store');

            Route::get('register', [AccountAuthController::class, 'showRegister'])
                ->name('account.register');
            Route::post('register', [AccountAuthController::class, 'register'])
                ->middleware('throttle:5,1')->name('account.register.store');

            /* ---------- نسيت كلمة المرور ---------- */
            // بتخدم اللوحة والموقع سوا — الحساب واحد
            Route::get('forgot-password', [PasswordResetController::class, 'showRequest'])
                ->name('password.request');
            Route::post('forgot-password', [PasswordResetController::class, 'sendLink'])
                ->middleware('throttle:5,10')->name('password.email');

            Route::get('reset-password/{token}', [PasswordResetController::class, 'showReset'])
                ->name('password.reset');
            Route::post('reset-password', [PasswordResetController::class, 'reset'])
                ->middleware('throttle:5,10')->name('password.update');
        });

        Route::middleware('auth')->group(function () {
            Route::post('logout', [AccountAuthController::class, 'logout'])
                ->name('account.logout');

            Route::get('account', [AccountController::class, 'index'])
                ->name('account.index');
            Route::get('account/favorites', [AccountController::class, 'favorites'])
                ->name('account.favorites');
            Route::get('account/requests', [AccountController::class, 'requests'])
                ->name('account.requests');
            Route::put('account', [AccountController::class, 'update'])
                ->name('account.update');

            Route::post('favorites/{property}', [FavoriteController::class, 'toggle'])
                ->whereNumber('property')->name('account.favorites.toggle');

            /* ---------- قيّم تجربتك ---------- */
            // رأي واحد للحساب — الحفظ بيعدّل الموجود مش بيضيف تاني
            Route::get('account/review', [AccountReviewController::class, 'edit'])->name('account.review');
            Route::post('account/review', [AccountReviewController::class, 'store'])
                ->middleware('throttle:10,60')->name('account.review.store');

            /* ---------- البحث المحفوظ ---------- */
            Route::get('account/saved-searches', [SavedSearchController::class, 'index'])->name('account.searches');
            Route::post('account/saved-searches', [SavedSearchController::class, 'store'])->name('account.searches.store');
            Route::put('account/saved-searches/{id}', [SavedSearchController::class, 'update'])->whereNumber('id')->name('account.searches.update');
            Route::delete('account/saved-searches/{id}', [SavedSearchController::class, 'destroy'])->whereNumber('id')->name('account.searches.destroy');

            /* ---------- وحدات المعلن ---------- */
            // على الصلاحية مش على الدور: الوسيط والشركة والمعلن كلهم
            // بيديروا وحداتهم من هنا، والفرق بينهم إن الأولانيين معاهم لوحة كمان
            Route::middleware('permission:manage listings')->group(function () {
                Route::get('account/my-properties', [AccountPropertyController::class, 'index'])->name('account.properties');
                Route::get('account/my-properties/create', [AccountPropertyController::class, 'create'])->name('account.properties.create');
                Route::post('account/my-properties', [AccountPropertyController::class, 'store'])->name('account.properties.store');
                Route::get('account/my-properties/{id}/edit', [AccountPropertyController::class, 'edit'])->whereNumber('id')->name('account.properties.edit');
                Route::put('account/my-properties/{id}', [AccountPropertyController::class, 'update'])->whereNumber('id')->name('account.properties.update');
                Route::post('account/my-properties/{id}/toggle', [AccountPropertyController::class, 'toggle'])->whereNumber('id')->name('account.properties.toggle');
                Route::post('account/my-properties/{id}/feature', [AccountPropertyController::class, 'requestFeature'])->whereNumber('id')->name('account.properties.feature');
                Route::delete('account/my-properties/{id}', [AccountPropertyController::class, 'destroy'])->whereNumber('id')->name('account.properties.destroy');
            });
        });

        // استقبال طلبات فورم "اتصل بنا" → موديول Leads
        Route::post('/leads', [LeadController::class, 'store'])
            ->middleware('throttle:8,1')
            ->name('leads.store');

        /* ---------- صفحات المحتوى — لازم تفضل آخر حاجة هنا ----------
         | `/{locale}/{slug}` بيلقط أي مقطع واحد، فأي راوت بيتضاف تحته
         | مش هيتشاف أبدًا. لارافيل بيطابق بترتيب التسجيل.
         | و`App\Support\ReservedSlugs` بيمنع الصفحة من الأساس إنها تاخد
         | اسم مسار موجود، فالحماية من الناحيتين.
         */
        Route::get('/{slug}', [PageController::class, 'show'])
            ->name('pages.show');

    });
