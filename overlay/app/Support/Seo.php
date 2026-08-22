<?php

namespace App\Support;

use Illuminate\Support\Str;
use Modules\Core\Services\SettingsService;

/**
 * ميتا الصفحات. بتتحسب في السيرفر وبتترندر في app.blade.php،
 * مش عن طريق <Head> بتاعة Inertia — من غير SSR دي بتتكتب بالجافاسكربت
 * بعد التحميل، وده مش كفاية لموقع عايش على البحث العضوي.
 */
class Seo
{
    public static function page(
        string $locale,
        string $title,
        ?string $description = null,
        ?string $image = null,
        string $type = 'website',
        array $jsonLd = [],
    ): array {
        $settings = app(SettingsService::class);
        $general = $settings->group('general');
        $seo = $settings->group('seo');

        $siteName = $general['site_name'] ?? config('app.name');
        $path = self::pathWithoutLocale();

        return [
            // ?? مطلوبة: مجموعة seo بتبقى فاضية على تثبيت جديد قبل ما الأدمن يحفظ حاجة
            'title' => $title !== '' ? "{$title} — {$siteName}" : (($seo['meta_title'] ?? '') ?: $siteName),
            // الوصف ممكن يكون فقرتين — الأسطر بتتحوّل مسافات عشان meta بيبقى سطر واحد
            'description' => Str::limit(
                trim(preg_replace('/\s+/u', ' ', strip_tags($description ?: (($seo['meta_description'] ?? '') ?: ($general['tagline'] ?? '')))) ?? ''),
                160,
            ),
            'canonical' => url("/{$locale}{$path}"),
            'alternates' => [
                'ar' => url("/ar{$path}"),
                'en' => url("/en{$path}"),
            ],
            // ترتيب المعاينة: صورة الصفحة نفسها ← صورة المعاينة العامة (1200×630) ← اللوجو.
            // اللوجو آخر حاجة عن قصد: مربع، وواتساب/تويتر بيرندروه كأيقونة صغيرة مش كارت كبير.
            ...self::image(
                $image
                    ?: (($seo['og_image'] ?? '') ?: $settings->get('branding', 'logo_path', '/images/logo.png'))
            ),
            'locale' => ($seo['og_locale'] ?? '') ?: ($locale === 'en' ? 'en_US' : 'ar_EG'),
            'type' => $type,
            'siteName' => $siteName,
            'jsonLd' => array_values(array_filter([self::organization(), ...$jsonLd])),
        ];
    }

    /**
     * بيانات صورة المعاينة: الرابط المطلق + الأبعاد الحقيقية.
     *
     * الأبعاد بتتقاس من الملف نفسه مش بتتفترض: واتساب وفيسبوك بيثقوا في
     * og:image:width/height ومبينزّلوش الصورة عشان يقيسوها، فلو كتبنا 1200×630
     * على صورة عقار 620×440 الكارت بيطلع مقصوص أو مترفوض.
     *
     * @return array{image:string, imageWidth:?int, imageHeight:?int, imageIsWide:bool}
     */
    public static function image(string $path): array
    {
        $url = url($path);
        [$width, $height] = self::dimensions($path);

        return [
            'image' => $url,
            'imageWidth' => $width,
            'imageHeight' => $height,
            // تحت 600 بكسل عرض، تويتر بيرجع لكارت صغير برضه — فمش بندّعي العكس.
            'imageIsWide' => $width !== null && $width >= 600 && $width > $height,
        ];
    }

    /** @return array{0:?int, 1:?int} */
    private static function dimensions(string $path): array
    {
        // الصور بتتقرا من القرص مش عبر HTTP — الملفات كلها تحت public/.
        $file = public_path(parse_url($path, PHP_URL_PATH) ?? $path);

        if (! is_file($file)) {
            return [null, null];
        }

        $size = @getimagesize($file);

        return $size ? [$size[0], $size[1]] : [null, null];
    }

    /** مسار الصفحة من غير بادئة اللغة — عشان hreflang و canonical */
    private static function pathWithoutLocale(): string
    {
        $path = '/'.ltrim(request()->path(), '/');
        $path = preg_replace('#^/(ar|en)#', '', $path) ?? '';

        return $path === '/' ? '' : $path;
    }

    public static function organization(): array
    {
        $settings = app(SettingsService::class);
        $general = $settings->group('general');
        $contact = $settings->group('contact');
        $social = array_values(array_filter($settings->group('social')));

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateAgent',
            'name' => $general['site_name'] ?? config('app.name'),
            'url' => url('/'),
            'logo' => url($settings->get('branding', 'logo_path', '/images/logo.png')),
            'telephone' => $contact['phone'] ?? null,
            'email' => $contact['email'] ?? null,
            'address' => filled($contact['address'] ?? null) ? [
                '@type' => 'PostalAddress',
                'streetAddress' => $contact['address'],
                'addressCountry' => 'EG',
            ] : null,
            'sameAs' => $social ?: null,
        ]);
    }

    /** مسار التنقّل — بيظهر في نتيجة جوجل تحت الرابط */
    public static function breadcrumb(string $locale, array $trail): array
    {
        $home = $locale === 'en' ? 'Home' : 'الرئيسية';
        $items = [['name' => $home, 'url' => url("/{$locale}")]];

        foreach ($trail as $name => $url) {
            $items[] = ['name' => $name, 'url' => url("/{$locale}{$url}")];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->map(fn ($i, $n) => [
                '@type' => 'ListItem',
                'position' => $n + 1,
                'name' => $i['name'],
                'item' => $i['url'],
            ])->all(),
        ];
    }

    /** قائمة عقارات — بتخلي جوجل يفهم إن دي صفحة نتايج */
    public static function itemList(array $rows, string $locale, string $path): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'numberOfItems' => count($rows),
            'itemListElement' => collect($rows)->take(20)->map(fn ($row, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $row['title'] ?? $row['name'] ?? '',
            ])->all(),
        ];
    }

    /** عقار واحد — بيخلي جوجل يعرض السعر وعدد الغرف تحت النتيجة */
    public static function residence(array $p, string $locale): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateListing',
            'name' => $p['title'],
            'description' => $p['description'] ?: null,
            'url' => url("/{$locale}/properties/{$p['slug']}"),
            'image' => array_map(fn ($i) => url($i), $p['gallery'] ?: []) ?: null,
            'identifier' => $p['ref'] ?: null,
            'inLanguage' => $locale,
            'offers' => self::offer($p['price'] ?? ''),
            'mainEntity' => array_filter([
                '@type' => 'Accommodation',
                'name' => $p['title'],
                'accommodationCategory' => $p['type'] ?: null,
                'numberOfBedrooms' => $p['beds'] ?: null,
                'numberOfBathroomsTotal' => $p['baths'] ?: null,
                'floorSize' => $p['size'] ? [
                    '@type' => 'QuantitativeValue',
                    'value' => $p['size'],
                    'unitCode' => 'MTK',
                ] : null,
                'address' => $p['area'] ? [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $p['area'],
                    'addressCountry' => 'EG',
                ] : null,
            ]),
        ]);
    }

    /** مشروع/كمبوند */
    public static function project(array $c, string $locale): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'ApartmentComplex',
            'name' => $c['name'],
            'description' => $c['desc'] ?: null,
            'url' => url("/{$locale}/compounds/{$c['slug']}"),
            'image' => array_map(fn ($i) => url($i), $c['gallery'] ?: []) ?: null,
            'inLanguage' => $locale,
            'address' => $c['area'] ? [
                '@type' => 'PostalAddress',
                'addressLocality' => $c['area'],
                'addressCountry' => 'EG',
            ] : null,
            'amenityFeature' => collect($c['features'] ?? [])->map(fn ($f) => [
                '@type' => 'LocationFeatureSpecification',
                'name' => $f,
                'value' => true,
            ])->all() ?: null,
        ]);
    }

    /** السعر متخزّن كنص منسّق ("EGP 4,850,000") — بنطلّع منه الرقم بس */
    private static function offer(string $price): ?array
    {
        $digits = preg_replace('/\D+/', '', $price) ?? '';

        if ($digits === '') {
            return null;
        }

        return [
            '@type' => 'Offer',
            'price' => $digits,
            'priceCurrency' => 'EGP',
            'availability' => 'https://schema.org/InStock',
        ];
    }

    public static function article(array $post, string $locale): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post['title'],
            'description' => $post['excerpt'] ?: null,
            'image' => $post['image'] ? url($post['image']) : null,
            'author' => $post['author'] ? ['@type' => 'Person', 'name' => $post['author']] : null,
            'datePublished' => $post['publishedAt'] ?? null,
            'inLanguage' => $locale,
            'mainEntityOfPage' => url("/{$locale}/blog/{$post['slug']}"),
        ]);
    }
}
