<?php

namespace Modules\Properties\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Properties\Models\Property;

/**
 * كروت «شوهدت مؤخرًا» للزائر غير المسجّل.
 *
 * المتصفح بيبعت الأرقام اللي عنده في localStorage والسيرفر بيرجّع
 * الكروت — مش بنخزّن الكارت نفسه في المتصفح عشان السعر والحالة
 * مايبقوش قدام مش متحدّثين.
 */
class RecentlyViewedController extends Controller
{
    /** أقصى عدد وحدات في القايمة */
    public const LIMIT = 8;

    public function __invoke(Request $request, string $locale): JsonResponse
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->take(self::LIMIT * 2)
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['properties' => []]);
        }

        $rows = Property::published()
            ->whereIn('id', $ids)
            ->with(['location', 'compound.developer', 'compound.location', 'developer'])
            ->get()
            // الترتيب اللي جه من المتصفح هو الصح — الأحدث زيارة الأول
            ->sortBy(fn (Property $p) => $ids->search($p->id))
            ->take(self::LIMIT);

        return response()->json([
            'properties' => $rows->map(fn (Property $p) => $p->toCard($locale))->values()->all(),
        ]);
    }
}
