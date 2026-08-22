<?php

namespace Modules\Leads\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Compounds\Models\Compound;
use Modules\Leads\Models\Lead;
use Modules\Properties\Models\Property;

/**
 * استقبال طلبات الموقع العام — الفورم في "اتصل بنا" بيبعت هنا الأول
 * وبعدين بيفتح واتساب، فالطلب بيتسجّل حتى لو العميل مكمّلش على واتساب.
 */
class LeadController extends Controller
{
    public function store(Request $request, string $locale): RedirectResponse
    {
        // مصيدة البوتس: الحقل ده مخفي فالإنسان مبيملاهوش أبدًا.
        // بنرجّع نجاح عادي عشان البوت ميعرفش إنه اتمسك.
        if (filled($request->input('website'))) {
            return back()->with('success', $locale === 'en' ? 'Request received ✅' : 'وصلنا طلبك ✅');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'area' => ['nullable', 'string', 'max:120'],
            'budget' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', 'string', 'max:40'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'compound_id' => ['nullable', 'integer', 'exists:compounds,id'],
        ]);

        $data['source'] = in_array($data['source'] ?? '', array_keys(Lead::SOURCES), true)
            ? $data['source']
            : 'contact';

        $data['status'] = 'new';

        // الطلب بيروح لصاحب الوحدة/المشروع — من غير ده كل الطلبات بتتكوّم عند الأدمن
        $property = filled($data['property_id'] ?? null) ? Property::find($data['property_id']) : null;
        $compound = filled($data['compound_id'] ?? null) ? Compound::find($data['compound_id']) : null;

        $data['owner_id'] = $property instanceof Property ? $property->owner_id : $compound?->owner_id;

        // العميل المسجّل بيشوف الطلب بعد كده في «طلباتي».
        // مالك الوحدات مستثنى: الطلب بيوصله في صندوق الطلبات أصلًا،
        // ومحطوط تاني في «طلباتي» كان بيبان كأنه هو اللي طالب وحدته.
        $user = $request->user();
        $data['user_id'] = $user && ! $user->ownsListings() ? $user->id : null;

        Lead::create($data);

        return back()->with('success', $locale === 'en' ? 'Request received ✅' : 'وصلنا طلبك ✅');
    }
}
