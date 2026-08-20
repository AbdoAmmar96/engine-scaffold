<?php

namespace Modules\Leads\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Leads\Models\Lead;

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
        ]);

        $data['source'] = in_array($data['source'] ?? '', array_keys(Lead::SOURCES), true)
            ? $data['source']
            : 'contact';

        $data['status'] = 'new';

        Lead::create($data);

        return back()->with('success', $locale === 'en' ? 'Request received ✅' : 'وصلنا طلبك ✅');
    }
}
