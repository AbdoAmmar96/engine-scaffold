<?php

namespace Modules\Core\Http\Controllers;

use App\Support\Seo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Leads\Models\Lead;
use Modules\Properties\Models\Property;

/**
 * مساحة العميل على الموقع: المحفوظات، طلباته، وبياناته.
 * مش لوحة تحكم — بتلبس تصميم الموقع نفسه عشان متبقاش نقلة مفاجئة للزائر.
 */
class AccountController extends Controller
{
    public function index(Request $request, string $locale): Response
    {
        $user = $request->user();

        return Inertia::render('Site/Account/Index', [
            'stats' => [
                'favorites' => $user->favorites()->count(),
                'requests' => $user->requests()->count(),
                'open' => $user->requests()->whereNotIn('status', ['won', 'lost'])->count(),
                // null للعميل العادي — الكارت مبيظهرش أصلًا
                'listings' => $user->ownsListings() ? $user->properties()->count() : null,
            ],
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ],
            'meta' => Seo::page($locale, $locale === 'en' ? 'My account' : 'حسابي'),
        ]);
    }

    public function favorites(Request $request, string $locale): Response
    {
        /** @var Collection<int, Property> $rows */
        $rows = $request->user()->favorites()
            ->where('properties.is_active', true)
            ->where('properties.status', 'published')
            ->with('location')
            ->orderByDesc('favorites.created_at')
            ->get();

        return Inertia::render('Site/Account/Favorites', [
            'properties' => $rows->map(fn (Property $p) => $p->toCard($locale))->all(),
            'meta' => Seo::page($locale, $locale === 'en' ? 'Saved properties' : 'العقارات المحفوظة'),
        ]);
    }

    public function requests(Request $request, string $locale): Response
    {
        /** @var Collection<int, Lead> $rows */
        $rows = $request->user()->requests()
            ->with(['property', 'compound'])
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Site/Account/Requests', [
            'requests' => $rows->map(fn (Lead $l) => [
                'id' => $l->id,
                'subject' => $l->subject($locale) ?: ($locale === 'en' ? 'General enquiry' : 'استفسار عام'),
                'link' => $l->property?->slug ? "/{$locale}/properties/{$l->property->slug}"
                    : ($l->compound?->slug ? "/{$locale}/compounds/{$l->compound->slug}" : null),
                'message' => $l->message ?: '',
                'status' => Lead::STATUSES[$l->status] ?? ['label' => $l->status, 'tone' => 'muted'],
                'date' => $l->created_at?->format('Y/m/d'),
            ])->all(),
            'meta' => Seo::page($locale, $locale === 'en' ? 'My requests' : 'طلباتي'),
        ]);
    }

    public function update(Request $request, string $locale): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [], [
            'name' => 'الاسم',
            'email' => 'الإيميل',
            'phone' => 'الموبايل',
            'password' => 'كلمة المرور',
        ]);

        // فاضية = سيبها زي ما هي (الكاست hashed في الموديل بيشفّرها)
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('success', $locale === 'en' ? 'Saved ✅' : 'اتحفظ ✅');
    }
}
