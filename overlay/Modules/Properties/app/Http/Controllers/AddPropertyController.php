<?php

namespace Modules\Properties\Http\Controllers;

use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Leads\Models\Lead;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;

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
    /** أقصى عدد صور في الطلب الواحد */
    private const MAX_IMAGES = 8;

    public function create(string $locale): Response
    {
        $en = $locale === 'en';

        return Inertia::render('Site/AddProperty', [
            'options' => [
                'types' => array_map(
                    fn (string $type) => ['value' => $type, 'label' => $en ? Property::TYPES[$type] : $type],
                    array_keys(Property::TYPES),
                ),
                'locations' => Location::where('is_active', true)->orderBy('sort')->orderBy('id')->get()
                    ->map(fn (Location $l) => ['value' => (string) $l->id, 'label' => $l->t('name', $locale)])->all(),
                'finishing' => collect(Property::FINISHING)
                    ->map(fn (array $labels, string $key) => ['value' => $key, 'label' => $labels[$en ? 'en' : 'ar']])
                    ->values()->all(),
                'maxImages' => self::MAX_IMAGES,
            ],
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

            'title' => ['required', 'string', 'max:180'],
            'purpose' => ['required', Rule::in(['sale', 'rent'])],
            'type' => ['required', Rule::in(array_keys(Property::TYPES))],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'price_amount' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'size' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'beds' => ['nullable', 'integer', 'min:0', 'max:50'],
            'baths' => ['nullable', 'integer', 'min:0', 'max:50'],
            'finishing' => ['nullable', Rule::in(array_keys(Property::FINISHING))],
            'floor' => ['nullable', 'string', 'max:40'],
            'delivery_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'down_payment' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'description' => ['nullable', 'string', 'max:3000'],

            'images' => ['nullable', 'array', 'max:'.self::MAX_IMAGES],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
        ], [], $this->attributes($locale));

        $images = $this->storeImages($request->file('images') ?? []);
        $user = $request->user();

        // الوسيط/الشركة بتبقى مالكة وحدتها من أول لحظة — الزائر لأ،
        // فوحدته بتبقى تحت إدارة المنصّة لحد ما الأدمن يوزّعها
        $owner = $user?->can('manage listings') ? $user->id : null;

        $property = Property::create([
            'title' => $data['title'],
            'purpose' => $data['purpose'],
            'type' => $data['type'],
            'location_id' => $data['location_id'] ?? null,
            'price_amount' => $data['price_amount'] ?? null,
            'size' => $data['size'] ?? 0,
            'beds' => $data['beds'] ?? 0,
            'baths' => $data['baths'] ?? 0,
            'finishing' => $data['finishing'] ?? null,
            'floor' => $data['floor'] ?? null,
            'delivery_year' => $data['delivery_year'] ?? null,
            'down_payment' => $data['down_payment'] ?? null,
            'description' => $data['description'] ?? null,
            'image' => $images[0] ?? null,
            'gallery' => implode("\n", array_slice($images, 1)),
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
            'user_id' => $user && ! $user->isStaff() ? $user->id : null,
        ]);

        return back()->with('success', $en
            ? 'Your property was received ✅ — our team reviews it and publishes it within 24 hours.'
            : 'وصلنا عقارك ✅ — الفريق بيراجعه وبينشره خلال ٢٤ ساعة.');
    }

    /**
     * الصور بتتخزّن بره مكتبة الميديا (media/) عن قصد: دي رفع من زوار
     * مش من الإدارة، وخلطهم بيلخبط شاشة المكتبة.
     *
     * @param  UploadedFile[]  $files
     * @return string[] مسارات عامة
     */
    private function storeImages(array $files): array
    {
        $paths = [];

        foreach (array_slice($files, 0, self::MAX_IMAGES) as $file) {
            $name = Str::lower(Str::random(16)).'.'.Str::lower($file->getClientOriginalExtension());
            $file->storeAs('uploads/listings', $name, 'public');

            $paths[] = '/storage/uploads/listings/'.$name;
        }

        return $paths;
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

    /** أسماء الحقول في رسائل الخطأ */
    private function attributes(string $locale): array
    {
        return $locale === 'en' ? [] : [
            'name' => 'الاسم',
            'phone' => 'الموبايل',
            'email' => 'الإيميل',
            'title' => 'عنوان الإعلان',
            'purpose' => 'الغرض',
            'type' => 'نوع العقار',
            'location_id' => 'المنطقة',
            'price_amount' => 'السعر',
            'size' => 'المساحة',
            'beds' => 'غرف النوم',
            'baths' => 'الحمامات',
            'finishing' => 'التشطيب',
            'floor' => 'الدور',
            'delivery_year' => 'سنة التسليم',
            'down_payment' => 'المقدم',
            'description' => 'الوصف',
            'images' => 'الصور',
            'images.*' => 'الصورة',
        ];
    }
}
