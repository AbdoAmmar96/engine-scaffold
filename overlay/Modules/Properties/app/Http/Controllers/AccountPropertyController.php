<?php

namespace Modules\Properties\Http\Controllers;

use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Marketing\Models\FeaturedAd;
use Modules\Properties\Models\Property;
use Modules\Properties\Support\HandlesListingInput;

/**
 * «وحداتي» — المعلن بيدير عروضه من حسابه على الموقع، مش من لوحة التحكم.
 *
 * كل استعلام هنا بيعدّي على mine() فالملكية مضمونة على القايمة وعلى أي
 * وصول بالـ id سوا — من غير التاني ده كان يكتب رقم في العنوان ويعدّل
 * وحدة مش بتاعته.
 *
 * والاعتماد بيفضل عند الإدارة: أي حفظ من هنا بيرجّع الوحدة للمراجعة.
 */
class AccountPropertyController extends Controller
{
    use HandlesListingInput;

    public function index(Request $request, string $locale): Response
    {
        $rows = $this->mine($request)
            ->withCount(['leads', 'favoritedBy'])
            ->with(['location', 'featuredAds'])
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Site/Account/Properties', [
            'properties' => $rows->map(fn (Property $p) => $this->row($p, $locale))->all(),
            'summary' => [
                'total' => $rows->count(),
                'published' => $rows->where('status', 'published')->count(),
                'pending' => $rows->where('status', 'pending')->count(),
                'views' => (int) $rows->sum('views_count'),
                'leads' => (int) $rows->sum('leads_count'),
            ],
            'meta' => Seo::page($locale, $locale === 'en' ? 'My listings' : 'وحداتي'),
        ]);
    }

    public function create(Request $request, string $locale): Response
    {
        return Inertia::render('Site/Account/PropertyForm', [
            'property' => null,
            'options' => $this->listingOptions($locale),
            'meta' => Seo::page($locale, $locale === 'en' ? 'Add a listing' : 'إضافة وحدة'),
        ]);
    }

    public function edit(Request $request, string $locale, int $id): Response
    {
        $property = $this->find($request, $id);

        return Inertia::render('Site/Account/PropertyForm', [
            'property' => $this->form($property),
            'options' => $this->listingOptions($locale),
            'meta' => Seo::page($locale, $locale === 'en' ? 'Edit listing' : 'تعديل الوحدة'),
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate($this->listingRules(), [], $this->listingAttributes());

        $images = $this->resolveImages(null, $data, $request->file('images') ?? []);

        Property::create($this->listingColumns($data, $images) + [
            'owner_id' => $request->user()->id,
            'status' => 'pending',
            'is_active' => true,
        ]);

        return redirect()
            ->route('account.properties', ['locale' => $locale])
            ->with('success', $locale === 'en'
                ? 'Added ✅ — it goes live once our team reviews it.'
                : 'اتضافت ✅ — هتنزل على الموقع بعد ما الفريق يراجعها.');
    }

    public function update(Request $request, string $locale, int $id): RedirectResponse
    {
        $property = $this->find($request, $id);
        $data = $request->validate($this->listingRules(), [], $this->listingAttributes());

        $images = $this->resolveImages($property, $data, $request->file('images') ?? []);

        $property->update($this->listingColumns($data, $images) + [
            // المباع/المؤجّر بيفضل زي ما هو — التعديل عليه مش إعادة عرض
            'status' => in_array($property->status, ['sold', 'rented'], true) ? $property->status : 'pending',
            'rejection_reason' => null,
        ]);

        return redirect()
            ->route('account.properties', ['locale' => $locale])
            ->with('success', $locale === 'en'
                ? 'Saved ✅ — it returns to review before it goes live again.'
                : 'اتحفظت ✅ — بترجع للمراجعة قبل ما تنزل تاني.');
    }

    /** إخفاء/إظهار من صاحبها — مستقل عن حالة المراجعة */
    public function toggle(Request $request, string $locale, int $id): RedirectResponse
    {
        $property = $this->find($request, $id);

        $property->update(['is_active' => ! $property->is_active]);

        return back()->with('success', $property->is_active
            ? ($locale === 'en' ? 'Listing is showing again ✅' : 'الوحدة رجعت تظهر ✅')
            : ($locale === 'en' ? 'Listing hidden' : 'الوحدة اتخفت'));
    }

    /**
     * طلب ترقية لإعلان مميّز.
     *
     * بيتعمل «في انتظار الموافقة» — التسويق بيراجعه ويحدد الفترة.
     * المعلن مبيحددش تواريخ بنفسه عشان مايحجزش المساحة لنفسه.
     */
    public function requestFeature(Request $request, string $locale, int $id): RedirectResponse
    {
        $property = $this->find($request, $id);

        if ($property->status !== 'published') {
            return back()->with('error', $locale === 'en'
                ? 'Only a live listing can be promoted.'
                : 'الترقية للوحدات المنشورة بس.');
        }

        $open = FeaturedAd::where('property_id', $property->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($open) {
            return back()->with('error', $locale === 'en'
                ? 'There is already an open request for this listing.'
                : 'فيه طلب مفتوح على الوحدة دي بالفعل.');
        }

        FeaturedAd::create([
            'position' => 'listing',
            'property_id' => $property->id,
            'requested_by' => $request->user()->id,
            'status' => 'pending',
        ]);

        return back()->with('success', $locale === 'en'
            ? 'Request sent ✅ — our team will get back to you with the slot and the price.'
            : 'اتبعت الطلب ✅ — الفريق هيرجعلك بالمساحة والسعر.');
    }

    public function destroy(Request $request, string $locale, int $id): RedirectResponse
    {
        $this->find($request, $id)->delete();

        return redirect()
            ->route('account.properties', ['locale' => $locale])
            ->with('success', $locale === 'en' ? 'Deleted' : 'اتمسحت');
    }

    /* ------------------------------------------------------------------ */

    /** وحدات المستخدم الحالي بس */
    private function mine(Request $request)
    {
        return Property::query()->where('owner_id', $request->user()->id);
    }

    /** 404 لو مش بتاعته — مش 403، عشان مايعرفش إن الرقم ده موجود أصلًا */
    private function find(Request $request, int $id): Property
    {
        return $this->mine($request)->findOrFail($id);
    }

    /** صف في جدول «وحداتي» */
    private function row(Property $property, string $locale): array
    {
        return [
            'id' => $property->id,
            'title' => $property->t('title', $locale),
            'slug' => $property->slug ?? '',
            'image' => $property->image ?: '/images/demo/property-1.jpg',
            'area' => $property->location?->t('name', $locale) ?? '',
            'price' => $property->priceLabel($locale),
            'ref' => $property->ref ?? '',
            'status' => Property::STATUSES[$property->status] ?? ['label' => $property->status, 'tone' => 'muted'],
            'rejection' => $property->rejection_reason ?? '',
            'live' => $property->status === 'published' && $property->is_active,
            'hidden' => ! $property->is_active,
            'views' => (int) $property->views_count,
            'saves' => (int) $property->favorited_by_count,
            'requests' => (int) $property->leads_count,
            'promotion' => $this->promotion($property),
        ];
    }

    /**
     * حالة الترقية للعرض: null يعني الزرار يظهر، وغير كده بنقول له
     * هي فين — الزرار بيختفي عشان مايبعتش طلب تاني.
     */
    private function promotion(Property $property): ?array
    {
        $ad = $property->featuredAds
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->sortByDesc('id')
            ->first();

        if (! $ad || ($ad->status === 'approved' && ! $ad->isLive() && $ad->ends_at?->isPast())) {
            return null;
        }

        return [
            'open' => in_array($ad->status, ['pending', 'approved'], true),
            'state' => $ad->stateLabel(),
            'reason' => $ad->rejection_reason ?? '',
        ];
    }

    /** قيم الفورم عند التعديل */
    private function form(Property $property): array
    {
        return [
            'id' => $property->id,
            'title' => $property->title,
            'purpose' => $property->purpose,
            'type' => $property->type ?? '',
            'location_id' => (string) ($property->location_id ?? ''),
            'price_amount' => (string) ($property->price_amount ?? ''),
            'size' => (string) ($property->size ?: ''),
            'beds' => (string) ($property->beds ?: ''),
            'baths' => (string) ($property->baths ?: ''),
            'finishing' => $property->finishing ?? '',
            'floor' => $property->floor ?? '',
            'delivery_year' => (string) ($property->delivery_year ?? ''),
            'down_payment' => (string) ($property->down_payment ?? ''),
            'description' => $property->description ?? '',
            'gallery' => $property->imagePaths(),
        ];
    }
}
