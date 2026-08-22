<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Properties\Models\Property;

/**
 * حفظ/إلغاء حفظ وحدة. زرار واحد بيقلب الحالة، والصفحة بترجع مكانها
 * (preserveScroll من ناحية Inertia) عشان مايحصلش قفزة في القائمة.
 */
class FavoriteController extends Controller
{
    public function toggle(Request $request, string $locale, int $property): RedirectResponse
    {
        $unit = Property::published()->findOrFail($property);

        // toggle بترجّع attached/detached فبنعرف نقول للعميل حصل إيه
        $result = $request->user()->favorites()->toggle([$unit->id]);
        $added = $result['attached'] !== [];

        return back()->with('success', $added
            ? ($locale === 'en' ? 'Saved to your list ✅' : 'اتحفظت في قائمتك ✅')
            : ($locale === 'en' ? 'Removed from your list' : 'اتشالت من قائمتك'));
    }
}
