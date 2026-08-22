<?php

namespace Modules\Properties\Http\Controllers;

use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Leads\Models\Lead;
use Modules\Properties\Models\Property;
use Modules\Properties\Support\HandlesListingInput;

/**
 * «أضف عقارك» — الفورم العام.
 *
 * الطلب بيتحوّل لحاجتين مع بعض: وحدة في انتظار المراجعة (عشان تدخل
 * دورة الاعتماد زي أي وحدة تانية بدل ما تتكتب تاني بالإيد)، وطلب في
 * صندوق الطلبات (عشان حد يكلّم صاحبها). الاتنين مربوطين ببعض.
 *
 * الوحدة مبتظهرش على الموقع قبل ما الأدمن يعتمدها — status = pending.
 */
class AddPropertyController extends Controller
{
    use HandlesListingInput;

    public function create(string $locale): Response
    {
        $en = $locale === 'en';

        return Inertia::render('Site/AddProperty', [
            'options' => $this->listingOptions($locale),
            'meta' => Seo::page(
                $locale,
                $en ? 'Add your property' : 'أضف عقارك',
                $en
                    ? 'List your unit for free. We review the details, then publish it with a reference number and get you buyer requests.'
                    : 'اعرض وحدتك مجانًا. بنراجع البيانات، وبعدها بتتنشر بكود مرجعي وبتوصلك طلبات المشترين.',
                null,
                'website',
                [Seo::breadcrumb($locale, [($en ? 'Add your property' : 'أضف عقارك') => '/add-property'])],
            ),
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $en = $locale === 'en';

        // نفس مصيدة البوتس بتاعة فورم اتصل بنا — نجاح شكلي من غير ما نكتب حاجة
        if (filled($request->input('website'))) {
            return back()->with('success', $en ? 'Received ✅' : 'وصلنا طلبك ✅');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            ...$this->listingRules(),
        ], [], [
            'name' => 'الاسم',
            'phone' => 'الموبايل',
            'email' => 'الإيميل',
            ...$this->listingAttributes(),
        ]);

        $images = $this->resolveImages(null, $data, $request->file('images') ?? []);
        $user = $request->user();

        // العميل اللي عرض وحدة بقى «معلن»: من غير كده الوحدة بتتسجّل
        // باسمه ومايقدرش يتابعها ولا يعدّلها من حسابه.
        if ($user?->hasRole('customer')) {
            $user->syncRoles(['lister']);
        }

        // الوسيط/الشركة/المعلن بيبقوا ملّاك وحدتهم من أول لحظة — الزائر
        // اللي مش مسجّل لأ، فوحدته تحت إدارة المنصّة لحد ما الأدمن يوزّعها
        $owner = $user?->ownsListings() ? $user->id : null;

        $property = Property::create($this->listingColumns($data, $images) + [
            'owner_id' => $owner,
            // القيمة صريحة: ديفولت الموديل «منشور»، وده مسار عام
            'status' => 'pending',
            'is_active' => true,
        ]);

        Lead::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'message' => $this->summary($data, $property),
            'source' => 'listing',
            'status' => 'new',
            'property_id' => $property->id,
            'owner_id' => $owner,
            'user_id' => $user && ! $user->ownsListings() ? $user->id : null,
        ]);

        return back()->with('success', $en
            ? 'Your property was received ✅ — our team reviews it and publishes it within 24 hours.'
            : 'وصلنا عقارك ✅ — الفريق بيراجعه وبينشره خلال ٢٤ ساعة.');
    }

    /** ملخّص الطلب في صندوق الطلبات — الأدمن يقرا الأساسي من غير ما يفتح الوحدة */
    private function summary(array $data, Property $property): string
    {
        $lines = ['عرض عقار من الموقع: '.$data['title']];

        if ($price = Property::money($data['price_amount'] ?? null)) {
            $lines[] = 'السعر المطلوب: '.$price;
        }

        if ($data['description'] ?? null) {
            $lines[] = $data['description'];
        }

        $lines[] = 'المراجعة: /admin/properties/'.$property->id.'/edit';

        return implode("\n", $lines);
    }
}
