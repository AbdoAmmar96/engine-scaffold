<?php

namespace Modules\Properties\Http\Controllers;

use App\Support\ResourceController;
use Illuminate\Validation\Rule;
use Modules\Compounds\Models\Compound;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;

class PropertyAdminController extends ResourceController
{
    protected function modelClass(): string { return Property::class; }
    protected function key(): string { return 'properties'; }

    protected function labels(): array
    {
        return ['plural' => 'العقارات', 'singular' => 'عقار'];
    }

    protected function searchable(): array { return ['title', 'title_en', 'ref']; }
    protected function with(): array { return ['location', 'compound']; }

    protected function columns(): array
    {
        return [
            'ref' => 'الكود',
            'title' => 'العقار',
            'location.name' => 'المنطقة',
            'purpose' => 'الغرض',
            'price' => 'السعر',
            'is_active' => 'مفعّل',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'title',       'label' => 'العنوان (عربي)',    'type' => 'text', 'required' => true],
            ['name' => 'title_en',    'label' => 'العنوان (إنجليزي)', 'type' => 'text'],
            ['name' => 'ref',         'label' => 'الكود',             'type' => 'text', 'hint' => 'مثال: XH-1001'],
            ['name' => 'location_id', 'label' => 'المنطقة',           'type' => 'select', 'options' => $this->options(Location::class, 'name')],
            ['name' => 'compound_id', 'label' => 'الكمبوند',          'type' => 'select', 'options' => $this->options(Compound::class, 'name')],
            ['name' => 'purpose',     'label' => 'الغرض',             'type' => 'select', 'options' => [
                ['value' => 'sale', 'label' => 'بيع'],
                ['value' => 'rent', 'label' => 'إيجار'],
            ]],
            ['name' => 'type',        'label' => 'النوع',             'type' => 'select', 'options' => array_map(
                fn ($t) => ['value' => $t, 'label' => $t],
                array_keys(Property::TYPES),
            )],
            ['name' => 'price',       'label' => 'السعر (عربي)',      'type' => 'text', 'hint' => 'مثال: EGP 4,850,000'],
            ['name' => 'price_en',    'label' => 'السعر (إنجليزي)',   'type' => 'text'],
            ['name' => 'beds',        'label' => 'غرف النوم',         'type' => 'number'],
            ['name' => 'baths',       'label' => 'الحمامات',          'type' => 'number'],
            ['name' => 'size',        'label' => 'المساحة (م²)',      'type' => 'number'],
            ['name' => 'image',       'label' => 'الصورة',            'type' => 'image'],
            ['name' => 'sort',        'label' => 'الترتيب',           'type' => 'number'],
            ['name' => 'is_active',   'label' => 'مفعّل',             'type' => 'toggle'],
        ];
    }

    protected function rules(?int $id): array
    {
        return [
            // الـ id بيوصل من الفورم، فلازم يتأكد إنه موجود فعلًا
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'compound_id' => ['nullable', 'integer', 'exists:compounds,id'],
            'ref' => ['nullable', 'string', 'max:40', Rule::unique('properties', 'ref')->ignore($id)],
        ];
    }

    private function options(string $model, string $col): array
    {
        return $model::orderBy($col)->pluck($col, 'id')
            ->map(fn ($name, $id) => ['value' => (string) $id, 'label' => $name])
            ->values()->all();
    }
}
