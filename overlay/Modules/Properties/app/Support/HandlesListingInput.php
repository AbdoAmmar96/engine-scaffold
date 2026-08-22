<?php

namespace Modules\Properties\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;

/**
 * حقول العرض المشتركة بين «أضف عقارك» العام و«وحداتي» في حساب المعلن.
 *
 * الاتنين بيكتبوا في نفس الجدول وبيعدّوا على نفس دورة الاعتماد، فلو كل
 * واحد كتب قواعده لوحده كانوا هيفرقوا بالهدوء — ويوم ما نضيف حقل يتحط
 * في واحد وينسى في التاني.
 */
trait HandlesListingInput
{
    /** أقصى عدد صور في العرض الواحد */
    protected const MAX_IMAGES = 8;

    /** @return array<string, mixed> */
    protected function listingRules(): array
    {
        return [
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
            // الصور القديمة اللي هتفضل — اللي مش هنا بتتشال من العرض
            'keep' => ['nullable', 'array', 'max:'.self::MAX_IMAGES],
            'keep.*' => ['string', 'max:400'],
        ];
    }

    /** أسماء الحقول في رسائل الخطأ */
    protected function listingAttributes(): array
    {
        return [
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

    /**
     * قيم الفورم → أعمدة الجدول. الصور بتتحسب هنا كمان عشان الصورة
     * الرئيسية تبقى دايمًا أول واحدة في المعرض، من غير تكرار.
     *
     * @param  string[]  $images  المسارات النهائية بترتيبها
     */
    protected function listingColumns(array $data, array $images): array
    {
        return [
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
        ];
    }

    /**
     * الصور النهائية: القديمة اللي المستخدم سابها + الجديدة اللي رفعها.
     *
     * @param  UploadedFile[]  $files
     * @return string[]
     */
    protected function resolveImages(?Property $property, array $data, array $files): array
    {
        $existing = $property ? $property->imagePaths() : [];

        // keep غايبة = مفيش حذف (فورم مش بيبعتها أصلًا)، مش = امسح الكل
        $keep = array_key_exists('keep', $data)
            ? array_values(array_intersect($existing, (array) $data['keep']))
            : $existing;

        return array_slice(array_values(array_unique([...$keep, ...$this->storeImages($files)])), 0, self::MAX_IMAGES);
    }

    /**
     * الصور بتتخزّن بره مكتبة الميديا (media/) عن قصد: دي رفع من زوار
     * مش من الإدارة، وخلطهم بيلخبط شاشة المكتبة.
     *
     * @param  UploadedFile[]  $files
     * @return string[] مسارات عامة
     */
    protected function storeImages(array $files): array
    {
        $paths = [];

        foreach (array_slice($files, 0, self::MAX_IMAGES) as $file) {
            $name = Str::lower(Str::random(16)).'.'.Str::lower($file->getClientOriginalExtension());
            $file->storeAs('uploads/listings', $name, 'public');

            $paths[] = '/storage/uploads/listings/'.$name;
        }

        return $paths;
    }

    /** خيارات الفورم — نفس المصدر في الصفحتين */
    protected function listingOptions(string $locale): array
    {
        $en = $locale === 'en';

        return [
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
        ];
    }
}
