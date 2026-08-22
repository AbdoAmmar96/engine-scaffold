import { test, expect } from '@playwright/test';

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
    { path: '/ar/blog', name: 'blog' },
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
