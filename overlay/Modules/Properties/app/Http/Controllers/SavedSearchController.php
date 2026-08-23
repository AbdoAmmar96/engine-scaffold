<?php

namespace Modules\Properties\Http\Controllers;

use App\Support\Catalog;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Properties\Models\Property;
use Modules\Properties\Models\SavedSearch;

/**
 * البحث المحفوظ + التنبيهات.
 *
 * الفلاتر بتتخزّن بعد ما تعدّي على Catalog::filters — يعني نفس التنضيف
 * اللي بيحصل على الرابط، فمفيش مفتاح غريب بيتخزّن في الداتابيز ويتنفّذ
 * بعدين في أمر الجدولة.
 */
class SavedSearchController extends Controller
{
    public function index(Request $request, string $locale): Response
    {
        $rows = $request->user()->savedSearches()->orderByDesc('id')->get();

        return Inertia::render('Site/Account/SavedSearches', [
            'searches' => $rows->map(fn (SavedSearch $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'summary' => $s->summary($locale),
                'url' => $s->url($locale),
                'alerts' => $s->alerts,
                'matches' => Catalog::countProperties($s->filters),
                'lastAlert' => $s->last_alert_at?->format('Y/m/d'),
            ])->all(),
            'meta' => Seo::page($locale, $locale === 'en' ? 'Saved searches' : 'البحث المحفوظ'),
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate(
            ['name' => ['nullable', 'string', 'max:80']],
            [],
            ['name' => 'اسم البحث'],
        );

        if ($user->savedSearches()->count() >= SavedSearch::LIMIT) {
            return back()->with('error', $locale === 'en'
                ? 'You reached the saved-search limit — delete one first.'
                : 'وصلت لأقصى عدد بحوث محفوظة — امسح واحد الأول.');
        }

        $filters = array_filter(Catalog::filters($request), fn ($v) => $v !== '' && $v !== null);

        if ($filters === []) {
            return back()->with('error', $locale === 'en'
                ? 'Pick at least one filter before saving the search.'
                : 'اختار فلتر واحد على الأقل قبل ما تحفظ البحث.');
        }

        $search = new SavedSearch([
            'name' => filled($data['name'] ?? null) ? $data['name'] : $this->autoName($filters, $locale),
            'filters' => $filters,
            // من دلوقتي ورايح: اللي موجود دلوقتي شافه بالفعل في الصفحة
            'last_property_id' => (int) Property::published()->max('id'),
        ]);

        $user->savedSearches()->save($search);

        return back()->with('success', $locale === 'en'
            ? 'Search saved ✅ — we\'ll email you when something matches.'
            : 'اتحفظ البحث ✅ — هنبعتلك إيميل أول ما ينزل اللي يطابقه.');
    }

    public function update(Request $request, string $locale, int $id): RedirectResponse
    {
        $search = $this->find($request, $id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'alerts' => ['boolean'],
        ], [], ['name' => 'اسم البحث']);

        $search->update($data);

        return back()->with('success', $locale === 'en' ? 'Saved ✅' : 'اتحفظ ✅');
    }

    public function destroy(Request $request, string $locale, int $id): RedirectResponse
    {
        $this->find($request, $id)->delete();

        return back()->with('success', $locale === 'en' ? 'Deleted' : 'اتمسح');
    }

    /** 404 لو مش بتاعه — البحث المحفوظ بيان شخصي */
    private function find(Request $request, int $id): SavedSearch
    {
        return $request->user()->savedSearches()->findOrFail($id);
    }

    /** اسم من الفلاتر نفسها — العميل يقدر يغيّره بعدين */
    private function autoName(array $filters, string $locale): string
    {
        $parts = collect($filters)
            ->map(fn ($v, $k) => Catalog::filterLabel($k, (string) $v, $locale))
            ->filter()
            ->take(3)
            ->all();

        return $parts ? implode(' · ', $parts) : ($locale === 'en' ? 'Saved search' : 'بحث محفوظ');
    }
}
