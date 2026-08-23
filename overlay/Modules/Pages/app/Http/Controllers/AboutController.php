<?php

namespace Modules\Pages\Http\Controllers;

use App\Support\Catalog;
use App\Support\Seo;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Services\SettingsService;

/**
 * صفحة «من نحن».
 *
 * كل نص فيها كان مكتوب جوّه React: «اثنتا عشرة سنة»، «فريق من 46 شخص»،
 * أربع محطات، وأربع صور ستوك بأسماء متلفّقة معروضة كأنها الفريق الحقيقي.
 * دلوقتي كله بيتقرا من مجموعة إعدادات `about`، **وكله فاضي افتراضيًا**:
 * القسم اللي مالوش بيانات بيختفي بدل ما يتلفّق — نفس القاعدة اللي الأرقام
 * ماشية عليها من قبل كده.
 *
 * الفاضي هنا مش نقص — الادعاء اللي محدش راجعه أسوأ من قسم ناقص.
 */
class AboutController extends Controller
{
    public function show(string $locale, SettingsService $settings): Response
    {
        $about = $settings->group('about');

        $title = $locale === 'en' ? 'About us' : 'من نحن';

        return Inertia::render('Site/About', [
            'content' => [
                'heroTitle' => $this->text($about, 'hero_title', $locale),
                'heroDesc' => $this->text($about, 'hero_desc', $locale),
                'pledgeTitle' => $this->text($about, 'pledge_title', $locale),
                // فقرة في كل سطر فاضي — نفس صيغة كتابة المقال
                'pledge' => $this->paragraphs($this->text($about, 'pledge_body', $locale)),
                'milestones' => $this->rows($about, 'milestones', $locale, ['year', 'title', 'text']),
                'team' => $this->rows($about, 'team', $locale, ['name', 'role', 'image']),
            ],
            'stats' => Catalog::stats($locale),
            'developers' => Catalog::developers($locale),
            'meta' => Seo::page(
                $locale,
                $title,
                $locale === 'en'
                    ? 'Who we are, how we work, and why we say no when a unit does not fit.'
                    : 'مين إحنا، وبنشتغل إزاي، وليه بنقول «لأ» لما الوحدة مش مناسبة.',
                null,
                'website',
                [Seo::breadcrumb($locale, [$title => '/about'])],
            ),
        ]);
    }

    /** قيمة المفتاح باللغة المطلوبة — الإنجليزي بيرجع للعربي لو فاضي */
    private function text(array $about, string $key, string $locale): string
    {
        if ($locale === 'en' && filled($about[$key.'_en'] ?? null)) {
            return trim((string) $about[$key.'_en']);
        }

        return trim((string) ($about[$key] ?? ''));
    }

    /** @return list<string> */
    private function paragraphs(string $text): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\n\s*\n/', $text) ?: []),
            fn ($p) => $p !== '',
        ));
    }

    /**
     * سطر لكل بند، والأعمدة مفصولة بـ `|`.
     * السطر الناقص بيتساب — أحسن من كارت نصه فاضي.
     *
     * @param  list<string>  $columns
     * @return list<array<string, string>>
     */
    private function rows(array $about, string $key, string $locale, array $columns): array
    {
        $out = [];

        foreach (preg_split('/\r\n|\r|\n/', $this->text($about, $key, $locale)) ?: [] as $line) {
            $parts = array_map('trim', explode('|', $line));

            if (count($parts) < count($columns) || in_array('', array_slice($parts, 0, count($columns)), true)) {
                continue;
            }

            $out[] = array_combine($columns, array_slice($parts, 0, count($columns)));
        }

        return $out;
    }
}
