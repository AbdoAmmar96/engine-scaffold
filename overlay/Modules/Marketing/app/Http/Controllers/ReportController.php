<?php

namespace Modules\Marketing\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Leads\Models\Lead;
use Modules\Marketing\Models\FeaturedAd;
use Modules\Properties\Models\Property;

/**
 * تقارير الأداء.
 *
 * كل رقم هنا محسوب من الجداول وقت الطلب — مفيش رقم مكتوب بالإيد ولا
 * جدول ملخّصات بيقع ورا الحقيقة. لو الشاشة بقت بطيئة مع بيانات أكبر،
 * الحل كاش على الاستعلام مش أرقام مخزّنة.
 */
class ReportController extends Controller
{
    /** مدى التقرير بالأيام */
    private const DAYS = 30;

    public function index(): Response
    {
        return Inertia::render('Admin/Reports/Index', [
            'days' => self::DAYS,
            'totals' => $this->totals(),
            'leadsBySource' => $this->leadsBySource(),
            'leadsByStatus' => $this->leadsByStatus(),
            'topAreas' => $this->topAreas(),
            'topProperties' => $this->topProperties(),
            'ads' => $this->ads(),
            'daily' => $this->daily(),
        ]);
    }

    private function totals(): array
    {
        $since = now()->subDays(self::DAYS);

        return [
            ['label' => 'وحدات منشورة', 'value' => Property::published()->count()],
            ['label' => 'تحت المراجعة', 'value' => Property::where('status', 'pending')->count()],
            ['label' => 'طلبات ('.self::DAYS.' يوم)', 'value' => Lead::where('created_at', '>=', $since)->count()],
            ['label' => 'طلبات مفتوحة', 'value' => Lead::whereNotIn('status', ['won', 'lost'])->count()],
            ['label' => 'مشاهدات الوحدات', 'value' => (int) Property::sum('views_count')],
            ['label' => 'حسابات', 'value' => User::count()],
        ];
    }

    private function leadsBySource(): array
    {
        // query builder مش Eloquent: النتيجة أعمدة محسوبة مش صفوف موديل
        $rows = DB::table('leads')
            ->selectRaw('source, count(*) as total')
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        return $rows->map(fn (object $r) => [
            'label' => Lead::SOURCES[$r->source] ?? (string) $r->source,
            'value' => (int) $r->total,
        ])->all();
    }

    private function leadsByStatus(): array
    {
        $rows = DB::table('leads')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // بنمشي على الحالات بترتيبها مش بترتيب النتيجة — القمع بيتقرا بالترتيب
        return collect(Lead::STATUSES)->map(fn (array $s, string $key) => [
            'label' => $s['label'],
            'tone' => $s['tone'],
            'value' => (int) ($rows[$key]->total ?? 0),
        ])->values()->all();
    }

    /**
     * أكتر المناطق طلبًا — من منطقة الوحدة اللي الطلب عليها،
     * وإلا المنطقة اللي العميل كتبها في الفورم.
     */
    private function topAreas(): array
    {
        $fromUnits = DB::table('leads')
            ->join('properties', 'properties.id', '=', 'leads.property_id')
            ->join('locations', 'locations.id', '=', 'properties.location_id')
            ->selectRaw('locations.name as name, count(*) as total')
            ->groupBy('locations.name');

        $typed = DB::table('leads')
            ->whereNull('property_id')
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->selectRaw('area as name, count(*) as total')
            ->groupBy('area');

        $merged = collect($fromUnits->get())
            ->concat($typed->get())
            ->groupBy('name')
            ->map(fn ($rows, $name) => ['label' => (string) $name, 'value' => (int) $rows->sum('total')])
            ->sortByDesc('value')
            ->take(8)
            ->values();

        return $merged->all();
    }

    private function topProperties(): array
    {
        return Property::published()
            ->withCount('leads')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get()
            ->map(fn (Property $p) => [
                'id' => $p->id,
                'label' => $p->title,
                'ref' => $p->ref ?? '',
                'views' => (int) $p->views_count,
                'leads' => (int) $p->leads_count,
            ])
            ->all();
    }

    private function ads(): array
    {
        return FeaturedAd::query()
            ->with(['property', 'compound'])
            ->orderByDesc('impressions')
            ->limit(10)
            ->get()
            ->map(fn (FeaturedAd $ad) => [
                'id' => $ad->id,
                'label' => $ad->subject('ar') ?: '—',
                'position' => FeaturedAd::POSITIONS[$ad->position]['label'] ?? $ad->position,
                'state' => $ad->stateLabel(),
                'impressions' => $ad->impressions,
                'clicks' => $ad->clicks,
                'ctr' => $ad->ctr(),
            ])
            ->all();
    }

    /** الطلبات يوم بيوم — الأيام الفاضية بتتحط بصفر عشان الخط مايكدبش */
    private function daily(): array
    {
        $rows = DB::table('leads')
            ->where('created_at', '>=', now()->subDays(self::DAYS)->startOfDay())
            ->selectRaw('date(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $out = [];

        for ($i = self::DAYS - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();

            $out[] = ['day' => substr($day, 5), 'value' => (int) ($rows[$day] ?? 0)];
        }

        return $out;
    }
}
