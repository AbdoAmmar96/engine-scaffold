import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';

/**
 * Smoke coverage for the public site + admin entry point.
 * Route list source of truth: `php artisan route:list --except-vendor`.
 */

const PUBLIC_PAGES = [
    { path: '/ar', name: 'home (ar)' },
    { path: '/en', name: 'home (en)' },
    { path: '/ar/properties', name: 'properties' },
    { path: '/ar/compounds', name: 'compounds' },
    { path: '/ar/about', name: 'about' },
    { path: '/ar/contact', name: 'contact' },
];

for (const page of PUBLIC_PAGES) {
    test(`${page.name} renders without console or network errors`, async ({ page: p }) => {
        const consoleErrors: string[] = [];
        const failedRequests: string[] = [];

        p.on('console', (msg) => {
            if (msg.type() === 'error') consoleErrors.push(msg.text());
        });
        p.on('response', (res) => {
            if (res.status() >= 400) failedRequests.push(`${res.status()} ${res.url()}`);
        });

        const response = await p.goto(page.path, { waitUntil: 'networkidle' });

        expect(response?.status(), `HTTP status for ${page.path}`).toBe(200);
        // Inertia mounts into #app — an empty root means the React bundle died.
        await expect(p.locator('#app')).not.toBeEmpty();
        expect(consoleErrors, `console errors on ${page.path}`).toEqual([]);
        expect(failedRequests, `failed requests on ${page.path}`).toEqual([]);
    });
}

test('root redirects to a locale', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL(/\/(ar|en)$/);
});

test('arabic pages are RTL', async ({ page }) => {
    await page.goto('/ar');
    await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
    await expect(page.locator('html')).toHaveAttribute('lang', 'ar');
});

test('english pages are LTR', async ({ page }) => {
    await page.goto('/en');
    await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
    await expect(page.locator('html')).toHaveAttribute('lang', 'en');
});

test('admin is gated behind login', async ({ page }) => {
    await page.goto('/admin');
    await expect(page).toHaveURL(/\/admin\/login/);
});

test('theme tokens reach the browser from the database', async ({ page }) => {
    await page.goto('/ar');
    // Token names come from the `theme` settings group with `_` → `-` (app.blade.php:25).
    const primary = await page.evaluate(() =>
        getComputedStyle(document.documentElement).getPropertyValue('--primary').trim()
    );
    // Proves the Theme Engine injected DB settings rather than falling back to build-time CSS.
    expect(primary).toMatch(/^#[0-9a-fA-F]{3,8}$/);
});

/**
 * Detail pages: a card that does not open is the whole point of the feature,
 * so the click path itself is asserted — not just the URL responding.
 */
test('property card opens the property page', async ({ page }) => {
    await page.goto('/ar/properties', { waitUntil: 'networkidle' });

    const card = page.locator('article a[href*="/ar/properties/"]').first();
    const href = await card.getAttribute('href');
    expect(href, 'property cards must link to a detail page').toMatch(/^\/ar\/properties\/.+/);

    await card.click();
    // `php artisan serve` بيرد على طلب واحد في المرة، والاختبارات بتشتغل بالتوازي —
    // فمهلة أطول من الافتراضية عشان الانتظار ميبقاش flaky
    await page.waitForURL(`**${href}`, { timeout: 20_000 });

    // بيانات الوحدة — العنوان والسعر ولوحة المواصفات
    await expect(page.locator('h1')).not.toBeEmpty();
    await expect(page.getByRole('heading', { name: 'بيانات الوحدة' })).toBeVisible();
});

test('compound card opens the compound page', async ({ page }) => {
    await page.goto('/ar/compounds', { waitUntil: 'networkidle' });

    const card = page.locator('article a[href*="/ar/compounds/"]').first();
    const href = await card.getAttribute('href');
    expect(href, 'compound cards must link to a detail page').toMatch(/^\/ar\/compounds\/.+/);

    await card.click();
    await page.waitForURL(`**${href}`, { timeout: 20_000 });
    await expect(page.getByRole('heading', { name: 'نظام السداد' })).toBeVisible();
});

test('unknown property slug is a 404, not a blank page', async ({ page }) => {
    const response = await page.goto('/ar/properties/no-such-unit');
    expect(response?.status()).toBe(404);
});

test('property page carries its own canonical, hreflang and JSON-LD', async ({ page }) => {
    await page.goto('/ar/properties', { waitUntil: 'networkidle' });
    const href = await page.locator('article a[href*="/ar/properties/"]').first().getAttribute('href');
    await page.goto(href!, { waitUntil: 'networkidle' });

    // الميتا بتترندر في السيرفر (app.blade.php) — من غير كده الصفحة مالهاش قيمة في البحث
    await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', new RegExp(`${href}$`));
    await expect(page.locator('link[hreflang="en"]')).toHaveCount(1);

    const types = await page.$$eval('script[type="application/ld+json"]', (nodes) =>
        nodes.map((n) => JSON.parse(n.textContent || '{}')['@type'])
    );
    expect(types).toContain('RealEstateListing');
    expect(types).toContain('BreadcrumbList');
});

test('link preview carries a wide image with its real dimensions', async ({ page }) => {
    await page.goto('/ar', { waitUntil: 'networkidle' });

    const attr = (sel: string) => page.locator(sel).getAttribute('content');
    const [image, width, height, card] = await Promise.all([
        attr('meta[property="og:image"]'),
        attr('meta[property="og:image:width"]'),
        attr('meta[property="og:image:height"]'),
        attr('meta[name="twitter:card"]'),
    ]);

    // واتساب/فيسبوك بيقروا الرابط المطلق بس — النسبي بيتجاهل والمعاينة بتطلع من غير صورة
    expect(image).toMatch(/^https?:\/\//);
    expect(card).toBe('summary_large_image');
    expect(Number(width)).toBeGreaterThanOrEqual(600);

    // الأبعاد لازم تطابق الملف فعلًا — رقم مكتوب بالغلط بيخلي الكارت مقصوص أو مترفوض
    const response = await page.request.get(image!);
    expect(response.status()).toBe(200);
    const measured = await page.evaluate(
        (src) =>
            new Promise<[number, number]>((resolve, reject) => {
                const img = new Image();
                img.onload = () => resolve([img.naturalWidth, img.naturalHeight]);
                img.onerror = reject;
                img.src = src;
            }),
        image!
    );
    expect(measured).toEqual([Number(width), Number(height)]);
});

test('sitemap lists the detail pages', async ({ page }) => {
    const response = await page.goto('/sitemap.xml');
    expect(response?.status()).toBe(200);

    const xml = await response!.text();
    expect(xml).toMatch(/<loc>[^<]*\/ar\/properties\/[^<]+<\/loc>/);
    expect(xml).toMatch(/<loc>[^<]*\/ar\/compounds\/[^<]+<\/loc>/);
});

/**
 * مساحة العميل. الحالات اللي بتغيّر بيانات متغطّاة في tests/Feature/RolePermissionsTest.php —
 * هنا بنتأكد بس إن الشاشات بترندر والحراسة شغّالة في المتصفح الحقيقي.
 */
for (const path of ['/ar/login', '/ar/register']) {
    test(`${path} renders without console errors`, async ({ page }) => {
        const consoleErrors: string[] = [];
        page.on('console', (msg) => {
            if (msg.type() === 'error') consoleErrors.push(msg.text());
        });

        const response = await page.goto(path, { waitUntil: 'networkidle' });

        expect(response?.status()).toBe(200);
        await expect(page.locator('form')).toBeVisible();
        expect(consoleErrors).toEqual([]);
    });
}

test('guest is sent to the site login, not the admin one', async ({ page }) => {
    await page.goto('/ar/account');
    await expect(page).toHaveURL(/\/ar\/login$/);
});

test('guest is sent to the admin login for the dashboard', async ({ page }) => {
    await page.goto('/admin/properties');
    await expect(page).toHaveURL(/\/admin\/login$/);
});

/**
 * أنماط خلفية الهيرو. النمط بيتغيّر من الداشبورد (theme.hero_variant)، ولو الفيديو
 * مش شغّال السبب شبه دايمًا إن النمط مش "video" — مش إن الملف مكسور.
 * التست ده بيثبّت اللي كل نمط بيعمله فعلًا.
 */
test('hero video actually plays when the variant is video', async ({ page }) => {
    await page.goto('/ar', { waitUntil: 'networkidle' });

    // النمط بيتقرا من الإعدادات مش من وجود العنصر — وإلا التست يتخطّى نفسه
    // بالظبط لما الفيديو يختفي، وهي الحالة اللي المفروض يمسكها
    const variant = await page.evaluate(
        () => JSON.parse(document.getElementById('app')!.dataset.page!).props.settings.theme.hero_variant
    );
    test.skip(variant !== 'video', `نمط الهيرو = ${variant}، فالفيديو مش مفروض يشتغل`);

    const video = page.locator('section video').first();
    await expect(video).toHaveCount(1);

    // مش بنكتفي بوجود العنصر: لازم يتحمّل ويمشي فعلًا
    await expect
        .poll(async () => video.evaluate((el: HTMLVideoElement) => el.readyState), { timeout: 15_000 })
        .toBeGreaterThanOrEqual(3);

    const state = await video.evaluate((el: HTMLVideoElement) => ({
        paused: el.paused,
        error: el.error?.message ?? null,
        width: el.videoWidth,
    }));

    expect(state.error).toBeNull();
    expect(state.paused).toBe(false);
    expect(state.width).toBeGreaterThan(0);
});

test('the hero background image comes from settings, not a hardcoded path', async ({ page }) => {
    await page.goto('/ar', { waitUntil: 'networkidle' });

    const src = await page.locator('section img').first().getAttribute('src');
    const settings = await page.evaluate(
        () => JSON.parse(document.getElementById('app')!.dataset.page!).props.settings.branding
    );

    expect(settings.hero_bg_image).toBeTruthy();
    expect(src).toBe(settings.hero_bg_image);
});

/** أقسام المطوّرين والمناطق والعقارات التجارية */
for (const path of ['/ar/developers', '/ar/areas', '/ar/properties/commercial', '/ar/properties/residential']) {
    test(`${path} renders without console errors`, async ({ page }) => {
        const consoleErrors: string[] = [];
        page.on('console', (msg) => {
            if (msg.type() === 'error') consoleErrors.push(msg.text());
        });

        const response = await page.goto(path, { waitUntil: 'networkidle' });

        expect(response?.status()).toBe(200);
        await expect(page.locator('h1')).not.toBeEmpty();
        expect(consoleErrors).toEqual([]);
    });
}

test('developer card opens the developer page', async ({ page }) => {
    await page.goto('/ar/developers', { waitUntil: 'networkidle' });

    const card = page.locator('a[href*="/ar/developers/"]').first();
    const href = await card.getAttribute('href');
    expect(href).toMatch(/^\/ar\/developers\/.+/);

    await card.click();
    await page.waitForURL(`**${href}`, { timeout: 20_000 });
    await expect(page.getByRole('heading', { name: 'مشاريع المطوّر', exact: true })).toBeVisible();
});

test('area card opens the area page', async ({ page }) => {
    await page.goto('/ar/areas', { waitUntil: 'networkidle' });

    const card = page.locator('a[href*="/ar/areas/"]').first();
    const href = await card.getAttribute('href');
    expect(href).toMatch(/^\/ar\/areas\/.+/);

    await card.click();
    await page.waitForURL(`**${href}`, { timeout: 20_000 });
    await expect(page.getByRole('heading', { name: 'مشاريع في المنطقة' })).toBeVisible();
});

test('the commercial section only lists commercial types', async ({ page }) => {
    // /properties/commercial لازم يوصل للقسم مش لصفحة وحدة اسمها commercial
    const response = await page.goto('/ar/properties/commercial', { waitUntil: 'networkidle' });
    expect(response?.status()).toBe(200);

    const types = await page.evaluate(() => {
        const el = document.getElementById('app');
        const page = JSON.parse(el?.getAttribute('data-page') || '{}');
        return (page.props?.properties ?? []).map((p: { category: string }) => p.category);
    });

    expect(types.length).toBeGreaterThan(0);
    expect([...new Set(types)]).toEqual(['commercial']);
});

/** لوحة الفلاتر والقائمة المنسدلة — منطق الفلترة نفسه متغطّى في ListingFiltersTest */
test('applying a filter puts it in the URL and narrows the results', async ({ page }) => {
    await page.goto('/ar/properties', { waitUntil: 'networkidle' });

    const before = await page.locator('article').count();
    expect(before).toBeGreaterThan(1);

    await page.getByRole('button', { name: /فلاتر متقدمة/ }).click();
    await page.locator('select').filter({ hasText: 'تشطيب كامل' }).first().selectOption('furnished');
    await page.getByRole('button', { name: /طبّق الفلاتر/ }).click();

    await page.waitForURL(/finishing=furnished/, { timeout: 20_000 });
    await expect(page.locator('article')).not.toHaveCount(before);
});

test('the services dropdown opens and links to the sections', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('/ar', { waitUntil: 'networkidle' });

    const trigger = page.getByRole('button', { name: /خدماتنا/ });
    await expect(trigger).toHaveAttribute('aria-expanded', 'false');

    await trigger.click();
    await expect(trigger).toHaveAttribute('aria-expanded', 'true');

    // نفس الأسماء موجودة في الفوتر كمان، فبنحصر البحث في الهيدر
    const header = page.locator('header');

    for (const [name, href] of [['العقارات', '/ar/properties'], ['عقارات تجارية', '/ar/properties/commercial'], ['الكمبوندات', '/ar/compounds']] as const) {
        await expect(header.getByRole('link', { name, exact: true })).toHaveAttribute('href', href);
    }
});

const noOverflow = (page: import('@playwright/test').Page) =>
    page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth);

test('the page does not scroll sideways on this device', async ({ page }) => {
    for (const path of ['/ar', '/ar/properties']) {
        await page.goto(path, { waitUntil: 'networkidle' });
        expect(await noOverflow(page), `horizontal overflow on ${path}`).toBe(true);
    }
});

test('the header fits at every desktop width', async ({ page, isMobile }) => {
    // تكبير جهاز موبايل لعرض ديسكتوب مش حالة حقيقية — بيدي طفح وهمي
    test.skip(isMobile, 'desktop-only widths');

    for (const width of [1024, 1280, 1440, 1920]) {
        await page.setViewportSize({ width, height: 800 });
        await page.goto('/ar', { waitUntil: 'networkidle' });

        expect(await noOverflow(page), `horizontal overflow at ${width}px`).toBe(true);
    }
});

/* ---------------------------------------------------------------------------
 | صفحات الهبوط البرمجية + أضف عقارك
 | التوليد والفلترة متغطّيين في LandingPageTest — هنا الشكل والتفاعل بس.
 ---------------------------------------------------------------------------- */

test('a landing page opens with its own heading and keeps its subject', async ({ page }) => {
    await page.goto('/ar/properties/apartments-for-sale-in-new-cairo', { waitUntil: 'networkidle' });

    await expect(page.getByRole('heading', { level: 1 })).toHaveText('شقق للبيع في القاهرة الجديدة');

    // أبعاد الصفحة مقفولة: مفيش منتقي نوع ولا غرض ولا منطقة يغيّر موضوعها
    await expect(page.locator('form select')).toHaveCount(1);
    await expect(page.getByText('القسم', { exact: true })).toHaveCount(0);

    const cards = await page.locator('article').count();
    expect(cards).toBeGreaterThan(0);
});

test('a landing page links to its neighbours', async ({ page }) => {
    await page.goto('/ar/properties/apartments-for-sale-in-new-cairo', { waitUntil: 'networkidle' });

    const related = page.getByRole('navigation').filter({ hasText: 'صفحات قريبة' }).getByRole('link');
    expect(await related.count()).toBeGreaterThan(0);

    await related.first().click();
    await page.waitForURL(/\/ar\/properties\/[a-z-]+/, { timeout: 20_000 });
    await expect(page.locator('#app')).not.toBeEmpty();
});

test('narrowing a landing page keeps it on the same page', async ({ page }) => {
    await page.goto('/ar/properties/apartments-for-sale-in-new-cairo', { waitUntil: 'networkidle' });

    await page.getByRole('button', { name: /فلاتر متقدمة/ }).click();
    await page.getByRole('button', { name: /طبّق الفلاتر/ }).click();

    await expect(page).toHaveURL(/\/ar\/properties\/apartments-for-sale-in-new-cairo/);
});

test('the add-property form shows its steps and refuses an empty submit', async ({ page }) => {
    await page.goto('/ar/add-property', { waitUntil: 'networkidle' });

    await expect(page.getByRole('heading', { level: 1 })).toHaveText('أضف عقارك');
    await expect(page.locator('fieldset')).toHaveCount(4);

    // الحقول المطلوبة بتتفحص في السيرفر — الفورم مالوش required عشان الرسالة تبقى عربية
    await page.getByRole('button', { name: /أرسل العقار للمراجعة/ }).click();

    await expect(page.getByText('حقل الاسم مطلوب.').first()).toBeVisible({ timeout: 20_000 });
});

/* ---------------------------------------------------------------------------
 | حساب المعلن + استعادة كلمة المرور
 | السلوك والصلاحيات متغطّيين في MyListingsTest و PasswordResetTest —
 | هنا الشاشات وطريق الزائر ليها.
 ---------------------------------------------------------------------------- */

test('the sign-in page offers a way out when you forget the password', async ({ page }) => {
    await page.goto('/ar/login', { waitUntil: 'networkidle' });

    await page.getByRole('link', { name: 'نسيت كلمة المرور؟' }).click();
    await page.waitForURL(/\/ar\/forgot-password/, { timeout: 20_000 });

    await expect(page.getByRole('heading', { level: 1 })).toHaveText('استعادة كلمة المرور');
});

test('asking for a reset link answers the same way for any address', async ({ page }) => {
    await page.goto('/ar/forgot-password', { waitUntil: 'networkidle' });

    await page.getByRole('textbox').fill('nobody-at-all@example.com');
    await page.getByRole('button', { name: /أرسل الرابط/ }).click();

    // نفس الرد للإيميل الموجود والمش موجود — الصفحة مش أداة تعداد حسابات
    await expect(page.getByText(/رابط التغيير في طريقه/)).toBeVisible({ timeout: 20_000 });
});

test('the listings area is behind the site login, not the admin one', async ({ page }) => {
    await page.goto('/ar/account/my-properties');

    await expect(page).toHaveURL(/\/ar\/login/);
});

test('the admin login links to the same password reset', async ({ page }) => {
    await page.goto('/admin/login', { waitUntil: 'networkidle' });

    await expect(page.getByRole('link', { name: 'نسيت كلمة المرور؟' })).toHaveAttribute(
        'href',
        '/ar/forgot-password',
    );
});

/* ---------------------------------------------------------------------------
 | المساحات الإعلانية · البحث المحفوظ · شوهدت مؤخرًا
 | المنطق متغطّي في FeaturedAdsTest و SavedSearchTest — هنا الشكل والتفاعل.
 ---------------------------------------------------------------------------- */

test('a sponsored slot is labelled as one', async ({ page }) => {
    await page.goto('/ar/properties', { waitUntil: 'networkidle' });

    const strip = page.getByText('إعلان مميّز', { exact: true });

    // مفيش مساحات مباعة على التثبيت الافتراضي — لو ظهرت لازم تبقى مكتوب عليها
    if ((await strip.count()) > 0) {
        await expect(strip.first()).toBeVisible();
    }
});

test('saving a search asks a guest to sign in first', async ({ page }) => {
    await page.goto('/ar/properties?purpose=sale', { waitUntil: 'networkidle' });

    const cta = page.getByRole('link', { name: /سجّل دخولك لحفظ البحث/ });

    await expect(cta).toHaveAttribute('href', '/ar/login');
});

test('the save-search button only appears once something is filtered', async ({ page }) => {
    await page.goto('/ar/properties', { waitUntil: 'networkidle' });

    await expect(page.getByText(/احفظ هذا البحث|سجّل دخولك لحفظ البحث/)).toHaveCount(0);
});

test('opening a unit remembers it in the browser', async ({ page }) => {
    await page.goto('/ar/properties', { waitUntil: 'networkidle' });
    await page.locator('article a').first().click();
    await page.waitForURL(/\/ar\/properties\/[^/]+$/, { timeout: 20_000 });

    const stored = await page.evaluate(() => window.localStorage.getItem('bp.recently-viewed'));

    expect(JSON.parse(stored ?? '[]').length).toBeGreaterThan(0);

    // وبيرجع على الرئيسية كقسم «شوهدت مؤخرًا»
    await page.goto('/ar', { waitUntil: 'networkidle' });
    await expect(page.getByText('شوهدت مؤخرًا').first()).toBeVisible({ timeout: 20_000 });
});

test('the admin reports and activity screens are gated', async ({ page }) => {
    for (const path of ['/admin/reports', '/admin/activity', '/admin/featured-ads']) {
        await page.goto(path);
        await expect(page).toHaveURL(/\/admin\/login/);
    }
});

/* ---------------------------------------------------------------------------
 | الطفح الأفقي
 |
 | الصنف ده بيعدّي من مراجعة الكود ومن اختبارات PHP بصمت: الصفحة بترندر
 | صح، والـHTTP بيرجّع 200، والمستخدم بس هو اللي بيلاقي نفسه بيسحب الشاشة
 | يمين وشمال. حصل فعلًا — لوحة التحكم فضلت شهور بسايدبار `ps-64` ثابت
 | فكانت مش قابلة للاستخدام من الموبايل، ومحدش اكتشفها من الكود.
 |
 | الاختبار بيتشغّل على الديسكتوب والموبايل من `playwright.config.ts`.
 ---------------------------------------------------------------------------- */

const NO_SCROLL_PAGES = [
    '/ar',
    '/ar/properties',
    '/ar/compounds',
    '/ar/developers',
    '/ar/areas',
    '/ar/about',
    '/ar/contact',
    '/ar/add-property',
    '/ar/login',
    '/ar/register',
    '/ar/forgot-password',
    '/admin/login',
    '/en',
    '/en/properties',
];

for (const path of NO_SCROLL_PAGES) {
    test(`${path} does not scroll sideways`, async ({ page }) => {
        await page.goto(path, { waitUntil: 'networkidle' });

        const { scrollWidth, clientWidth } = await page.evaluate(() => ({
            scrollWidth: document.documentElement.scrollWidth,
            clientWidth: document.documentElement.clientWidth,
        }));

        // بكسل سماح للتقريب في القياس
        expect(scrollWidth, `${path} بيطفح ${scrollWidth - clientWidth}px`).toBeLessThanOrEqual(clientWidth + 1);
    });
}


/* ---------------------------------------------------------------------------
 | تنسيق الهيدر
 |
 | صنف تاني بيعدّي بصمت: الصفحة مبتسحبش يمين وشمال، فاختبار الطفح بيعدّي،
 | ومع ذلك القائمة بتلمس الشعار أو الأزرار وبتبقى مكرمشة.
 |
 | حصل فعلًا: البادينج كان `2xl:px-3.5`، والهيدر محدود بـ `max-w-7xl`
 | يعني 1280 مهما كبرت الشاشة — فعند 1536 البادينج كبر جوه حاوية ما كبرتش
 | والقائمة خرجت 36 بكسل عن إطارها ولمست الطرفين.
 |
 | وعدد اللينكات نفسه متغيّر: قفل قسم من الإعدادات بيشيل واحد، فالتنسيق
 | لازم يفضل متوازن بالعددين.
 ---------------------------------------------------------------------------- */

for (const width of [1280, 1536, 1920]) {
    test(`the header nav keeps its distance at ${width}px`, async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'desktop', 'القائمة الأفقية بتظهر من 1280 فوق');

        await page.setViewportSize({ width, height: 900 });
        await page.goto('/ar', { waitUntil: 'networkidle' });

        const gap = await page.evaluate(() => {
            const row = document.querySelector('header > div');
            if (!row) return null;

            const [logo, nav, actions] = [...row.children];
            const items = [...nav.children].map((el) => el.getBoundingClientRect());
            if (items.length === 0) return null;

            const box = nav.getBoundingClientRect();
            const start = Math.min(...items.map((i) => i.left));
            const end = Math.max(...items.map((i) => i.right));

            return {
                // اللينكات خرجت عن إطار <nav>؟
                spill: Math.max(0, box.left - start) + Math.max(0, end - box.right),
                // rtl: الشعار يمين واللينكات شماله، والأزرار شمال واللينكات يمينها
                toLogo: logo.getBoundingClientRect().left - end,
                toActions: start - actions.getBoundingClientRect().right,
            };
        });

        expect(gap).not.toBeNull();
        expect(gap!.spill).toBe(0);
        expect(gap!.toLogo).toBeGreaterThanOrEqual(16);
        expect(gap!.toActions).toBeGreaterThanOrEqual(16);

        // الحارس الحقيقي: الصف محدود بـ max-w-7xl فعرضه ثابت، فأي مقاس جوّه
        // مايتغيرش بعرض الشاشة. الفحص ده بيمسك العطل حتى لو عدد اللينكات
        // الحالي صغير كفاية إنه يستحمل التوسيع.
        const scale = await page.evaluate(() => {
            const link = document.querySelector('header nav a');
            const row = document.querySelector('header > div');

            return {
                padding: getComputedStyle(link!).paddingInline,
                fontSize: getComputedStyle(link!).fontSize,
                rowWidth: Math.round(row!.getBoundingClientRect().width),
            };
        });

        expect(scale.padding).toBe('10px');
        expect(scale.fontSize).toBe('13.5px');
        expect(scale.rowWidth).toBeLessThanOrEqual(1280);
    });
}


/* ---------------------------------------------------------------------------
 | قسم المدونة — مقفول افتراضيًا
 |
 | الافتراضي إخفاء، فصفحة المدونة مش في PUBLIC_PAGES: هي 404 على التثبيت
 | النضيف. بس الصفحة نفسها لازم تفضل متغطّية لما العميل يفتحها، فالمجموعة
 | دي بتفتح القسم وتقفله تاني.
 |
 | serial + afterAll: التبديل بيغيّر حالة عامة، فممنوع يتداخل مع نفسه،
 | ولازم يترجّع حتى لو الاختبار وقع في النص.
 ---------------------------------------------------------------------------- */

const artisan = (code: string) =>
    execFileSync('php', ['artisan', 'tinker', '--execute', code], {
        cwd: process.cwd(),
        encoding: 'utf8',
    });

/**
 * بيبدّل القسم ويتأكد إنه اتبدّل فعلًا.
 *
 * التأكيد مش زيادة: أول نسخة من الدالة دي كتبت `Modules\Core\...` جوه
 * template literal، والـ `\C` اتبلع كهروب فوصل اسم الكلاس من غير شرطات،
 * وtinker وقع، ورجّع صفر — فالاختبار عدّى أخضر وهو مش بيبدّل حاجة.
 */
const setBlog = (on: '0' | '1') => {
    artisan(
        `app(Modules\\Core\\Services\\SettingsService::class)->setMany('general', ['blog_enabled' => '${on}']);`,
    );

    const now = artisan(
        `echo app(Modules\\Core\\Services\\SettingsService::class)->get('general', 'blog_enabled');`,
    ).trim();

    if (now !== on) {
        throw new Error(`فشل تبديل المدونة: المطلوب ${on} والموجود ${now || '(فاضي)'}`);
    }
};

test.describe.serial('the blog section', () => {
    // مشروع واحد بس: serial بيرتّب جوه المشروع مش بين المشاريع — ولو desktop
    // و mobile بدّلوا نفس الإعداد في نفس اللحظة الاختبار بيبقى flaky
    const desktopOnly = 'التبديل بيغيّر حالة عامة، فبيتشغّل في مشروع واحد';

    test.afterAll(() => setBlog('0'));

    test('is a 404 while it is closed', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'desktop', desktopOnly);

        setBlog('0');

        const res = await page.goto('/ar/blog');

        expect(res?.status()).toBe(404);
    });

    test('renders without console or network errors once opened', async ({ page }, testInfo) => {
        test.skip(testInfo.project.name !== 'desktop', desktopOnly);

        setBlog('1');

        const consoleErrors: string[] = [];
        const failedRequests: string[] = [];

        page.on('console', (msg) => {
            if (msg.type() === 'error') consoleErrors.push(msg.text());
        });
        page.on('response', (res) => {
            if (res.status() >= 400) failedRequests.push(`${res.status()} ${res.url()}`);
        });

        const res = await page.goto('/ar/blog', { waitUntil: 'networkidle' });

        expect(res?.status()).toBe(200);
        await expect(page.locator('#app')).not.toBeEmpty();
        expect(consoleErrors).toEqual([]);
        expect(failedRequests).toEqual([]);
    });
});
