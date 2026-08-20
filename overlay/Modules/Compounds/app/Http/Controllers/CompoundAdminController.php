<?php

namespace Modules\Compounds\Http\Controllers;

use App\Support\ResourceController;
use Modules\Compounds\Models\Compound;
use Modules\Developers\Models\Developer;
use Modules\Locations\Models\Location;

class CompoundAdminController extends ResourceController
{
    protected function modelClass(): string { return Compound::class; }
    protected function key(): string { return 'compounds'; }

    protected function labels(): array
    {
        return ['plural' => 'الكمبوندات', 'singular' => 'كمبوند'];
    }

    protected function searchable(): array { return ['name', 'name_en']; }
    protected function with(): array { return ['developer', 'location']; }

    protected function columns(): array
    {
        return [
            'name' => 'الكمبوند',
            'developer.name' => 'المطوّر',
            'location.name' => 'المنطقة',
            'starting_price' => 'يبدأ من',
            'delivery' => 'التسليم',
            'is_active' => 'مفعّل',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name',           'label' => 'الاسم (عربي)',      'type' => 'text', 'required' => true],
            ['name' => 'name_en',        'label' => 'الاسم (إنجليزي)',   'type' => 'text'],
            ['name' => 'developer_id',   'label' => 'المطوّر',           'type' => 'select', 'options' => $this->options(Developer::class)],
            ['name' => 'location_id',    'label' => 'المنطقة',           'type' => 'select', 'options' => $this->options(Location::class)],
            ['name' => 'description',    'label' => 'الوصف (عربي)',      'type' => 'textarea'],
            ['name' => 'description_en', 'label' => 'الوصف (إنجليزي)',   'type' => 'textarea'],
            ['name' => 'starting_price', 'label' => 'يبدأ من',           'type' => 'text', 'hint' => 'مثال: EGP 5,400,000'],
            ['name' => 'down_payment',   'label' => 'المقدم',            'type' => 'text', 'hint' => 'مثال: 5%'],
            ['name' => 'installment_years',    'label' => 'التقسيط (عربي)',    'type' => 'text', 'hint' => 'مثال: 8 سنوات'],
            ['name' => 'installment_years_en', 'label' => 'التقسيط (إنجليزي)', 'type' => 'text'],
            ['name' => 'delivery',       'label' => 'التسليم',           'type' => 'text', 'hint' => 'مثال: Q4 2027'],
            ['name' => 'image',          'label' => 'الصورة',            'type' => 'image'],
            ['name' => 'is_new',         'label' => 'إطلاق جديد',        'type' => 'toggle'],
            ['name' => 'sort',           'label' => 'الترتيب',           'type' => 'number'],
            ['name' => 'is_active',      'label' => 'مفعّل',             'type' => 'toggle'],
        ];
    }

    protected function rules(?int $id): array
    {
        return [
            // الـ id بيوصل من الفورم، فلازم يتأكد إنه موجود فعلًا
            'developer_id' => ['nullable', 'integer', 'exists:developers,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
        ];
    }

    private function options(string $model): array
    {
        return $model::orderBy('name')->pluck('name', 'id')
            ->map(fn ($name, $id) => ['value' => (string) $id, 'label' => $name])
            ->values()->all();
    }
}
