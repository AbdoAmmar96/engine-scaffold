<?php

namespace Modules\Locations\Http\Controllers;

use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Modules\Locations\Models\Location;

class LocationAdminController extends ResourceController
{
    protected function modelClass(): string
    {
        return Location::class;
    }

    protected function key(): string
    {
        return 'locations';
    }

    protected function labels(): array
    {
        return ['plural' => 'المناطق', 'singular' => 'منطقة'];
    }

    protected function searchable(): array
    {
        return ['name', 'name_en'];
    }

    protected function columns(): array
    {
        return [
            'name' => 'المنطقة',
            'note' => 'الوصف',
            'is_featured' => 'مميّزة',
            'sort' => 'الترتيب',
            'is_active' => 'مفعّلة',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name',      'label' => 'الاسم (عربي)',      'type' => 'text',   'required' => true],
            ['name' => 'name_en',   'label' => 'الاسم (إنجليزي)',   'type' => 'text'],
            ['name' => 'slug',      'label' => 'رابط الصفحة',       'type' => 'text',
                'hint' => 'سيبه فاضي يتولّد من الاسم — /ar/areas/<الرابط>'],
            ['name' => 'note',      'label' => 'وصف مختصر (عربي)',  'type' => 'text',   'hint' => 'مثال: التجمع الخامس · الرحاب · مدينتي'],
            ['name' => 'note_en',   'label' => 'وصف مختصر (إنجليزي)', 'type' => 'text'],
            ['name' => 'about',     'label' => 'نبذة الصفحة (عربي)', 'type' => 'textarea', 'hint' => 'فقرة في كل سطر — بتتعرض في صفحة المنطقة'],
            ['name' => 'about_en',  'label' => 'نبذة الصفحة (إنجليزي)', 'type' => 'textarea'],
            ['name' => 'image',     'label' => 'الصورة',            'type' => 'image',  'hint' => 'مسار داخل public — مثال: /images/demo/area-1.jpg'],
            ['name' => 'cover',     'label' => 'غلاف الصفحة',       'type' => 'image',  'hint' => 'خلفية هيرو صفحة المنطقة — فاضي يستخدم الصورة'],
            ['name' => 'is_featured', 'label' => 'منطقة مميّزة',    'type' => 'toggle', 'hint' => 'بتظهر الأول في صفحة المناطق'],
            ['name' => 'sort',      'label' => 'الترتيب',           'type' => 'number'],
            ['name' => 'is_active', 'label' => 'مفعّلة',            'type' => 'toggle'],
        ];
    }

    protected function rules(?int $id): array
    {
        return [
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[\p{L}\p{N}-]+$/u', Rule::unique('locations', 'slug')->ignore($id)],
            'about' => ['nullable', 'string', 'max:8000'],
            'about_en' => ['nullable', 'string', 'max:8000'],
        ];
    }

    protected function transform(array $data, ?Model $model): array
    {
        if (blank($data['slug'] ?? null)) {
            $data['slug'] = Location::buildSlug($data['name'], $data['name_en'] ?? null, $model ? (int) $model->getKey() : null);
        }

        return $data;
    }

    /** المنطقة اللي عليها وحدات أو مشاريع مايتمسحش */
    /** الحذف قرار نهائي — مدخل البيانات بيدخل ويعدّل بس */
    protected function deletePermission(): ?string
    {
        return 'publish listings';
    }

    protected function guardDelete(Model $model): ?string
    {
        /** @var Location $model */
        $used = $model->properties()->count() + $model->compounds()->count();

        return $used > 0
            ? "المنطقة دي مربوطة بـ {$used} وحدة/مشروع — غيّر منطقتهم الأول."
            : null;
    }
}
