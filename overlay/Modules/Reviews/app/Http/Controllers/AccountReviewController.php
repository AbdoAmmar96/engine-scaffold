<?php

namespace Modules\Reviews\Http\Controllers;

use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Compounds\Models\Compound;
use Modules\Reviews\Models\Review;

/**
 * «قيّم تجربتك» — العميل بيكتب رأيه من حسابه.
 *
 * ده اللي بيخلّي التقييمات حاجة حقيقية مش نص تسويقي: الرأي مربوط بحساب
 * موجود، و`source = site` بيميّزه في اللوحة عن اللي الأدمن كتبه.
 *
 * رأي واحد للحساب — مش تعليقات. لو عدّله بيرجع «تحت المراجعة» تاني،
 * زي الوحدة بالظبط: نص اتعتمد ممنوع يتغيّر بعد الاعتماد من غير مراجعة.
 */
class AccountReviewController extends Controller
{
    public function edit(Request $request, string $locale): Response
    {
        $review = $this->mine($request);

        return Inertia::render('Site/Account/Review', [
            'review' => $review ? [
                'body' => $review->body,
                'rating' => $review->rating,
                'compound_id' => $review->compound_id,
                'status' => $review->status,
                'statusLabel' => $review->statusLabel(),
            ] : null,
            'compounds' => Compound::where('is_active', true)->orderBy('name')->get()
                ->map(fn (Compound $c) => ['value' => (string) $c->id, 'label' => $c->name])->all(),
            'meta' => Seo::page($locale, $locale === 'en' ? 'Rate your experience' : 'قيّم تجربتك'),
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:20', 'max:4000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'compound_id' => ['nullable', 'integer', Rule::exists('compounds', 'id')],
        ], [], [
            'body' => 'الرأي',
            'rating' => 'التقييم',
        ]);

        $user = $request->user();
        $review = $this->mine($request) ?? new Review;

        $review->fill($data + [
            'user_id' => $user->id,
            'author' => $user->displayName(),
            'source' => 'site',
            // أي تعديل بيرجّعه للمراجعة — نفس قاعدة الوحدات
            'status' => 'pending',
            'published_at' => null,
        ])->save();

        return back()->with('success', $locale === 'en'
            ? 'Thanks ✅ — we review it before it goes on the site.'
            : 'شكرًا ✅ — بنراجعه قبل ما ينزل على الموقع.');
    }

    private function mine(Request $request): ?Review
    {
        return Review::where('user_id', $request->user()->id)->first();
    }
}
