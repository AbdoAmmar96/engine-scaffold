<?php

namespace Modules\Properties\Http\Controllers;

use App\Support\OwnedResource;
use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Modules\Compounds\Models\Compound;
use Modules\Developers\Models\Developer;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;

class PropertyAdminController extends ResourceController
{
    use OwnedResource;

    protected function modelClass(): string
    {
        return Property::class;
    }

    protected function key(): string
    {
        return 'properties';
    }

    protected function labels(): array
    {
        return ['plural' => 'العقارات', 'singular' => 'عقار'];
    }

    protected function searchable(): array
    {
        return ['title', 'title_en', 'ref'];
    }

    protected function with(): array
    {
        return ['location', 'compound.developer', 'developer', 'owner'];
    }

    protected function columns(): array
    {
        return array_filter([
            'ref' => 'الكود',
            'title' => 'العقار',
            'location.name' => 'المنطقة',
            // عمود المالك مالوش لازمة للوسيط — كله بتاعه أصلًا
            'owner.name' => self::actorSeesEverything() ? 'الحساب المالك' : null,
            'purpose' => 'الغرض',
            'price' => 'السعر',
            'is_active' => 'مفعّل',
        ]);
    }

    protected function fields(): array
    {
        return [
            ...$this->ownerField(),
            ['name' => 'title',       'label' => 'العنوان (عربي)',    'type' => 'text', 'required' => true],
            ['name' => 'title_en',    'label' => 'العنوان (إنجليزي)', 'type' => 'text'],
            ['name' => 'slug',        'label' => 'رابط الصفحة',       'type' => 'text', 'hint' => 'سيبه فاضي يتولّد من العنوان — /ar/properties/<الرابط>'],
            ['name' => 'ref',         'label' => 'الكود',             'type' => 'text', 'hint' => 'مثال: XH-1001'],
            ['name' => 'location_id', 'label' => 'المنطقة',           'type' => 'select', 'options' => $this->options(Location::class, 'name')],
            ['name' => 'compound_id', 'label' => 'الكمبوند',          'type' => 'select', 'options' => $this->compoundOptions()],
            ['name' => 'developer_id', 'label' => 'المطوّر',          'type' => 'select', 'options' => $this->options(Developer::class, 'name'),
                'hint' => 'سيبه فاضي لو الوحدة جوه كمبوند — بتاخد مطوّر الكمبوند تلقائيًا. املاه لإعادة البيع والوحدات المستقلة.'],
            ['name' => 'purpose',     'label' => 'الغرض',             'type' => 'select', 'options' => [
                ['value' => 'sale', 'label' => 'بيع'],
                ['value' => 'rent', 'label' => 'إيجار'],
            ]],
            ['name' => 'type',        'label' => 'النوع',             'type' => 'select', 'options' => array_map(
                fn ($t) => ['value' => $t, 'label' => $t],
                array_keys(Property::TYPES),
            )],
            ['name' => 'description',    'label' => 'الوصف (عربي)',      'type' => 'textarea'],
            ['name' => 'description_en', 'label' => 'الوصف (إنجليزي)',   'type' => 'textarea'],
            ['name' => 'features',       'label' => 'المميزات (عربي)',   'type' => 'textarea', 'hint' => 'ميزة في كل سطر'],
            ['name' => 'features_en',    'label' => 'المميزات (إنجليزي)', 'type' => 'textarea', 'hint' => 'ميزة في كل سطر'],
            ['name' => 'price',       'label' => 'السعر (عربي)',      'type' => 'text', 'hint' => 'مثال: EGP 4,850,000'],
            ['name' => 'price_en',    'label' => 'السعر (إنجليزي)',   'type' => 'text'],
            ['name' => 'beds',        'label' => 'غرف النوم',         'type' => 'number'],
            ['name' => 'baths',       'label' => 'الحمامات',          'type' => 'number'],
            ['name' => 'size',        'label' => 'المساحة (م²)',      'type' => 'number'],
            ['name' => 'image',       'label' => 'الصورة الرئيسية',   'type' => 'image'],
            ['name' => 'gallery',     'label' => 'صور إضافية',        'type' => 'gallery'],
            ['name' => 'sort',        'label' => 'الترتيب',           'type' => 'number'],
            ['name' => 'is_active',   'label' => 'مفعّل',             'type' => 'toggle'],
        ];
    }

    protected function rules(?int $id): array
    {
        return $this->ownerRules() + [
            // الـ id بيوصل من الفورم، فلازم يتأكد إنه موجود فعلًا
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            // Rule::in على المشاريع اللي من حقه — مايقدرش يعلّق وحدته على مشروع غيره
            'compound_id' => ['nullable', 'integer', Rule::in(array_column($this->compoundOptions(), 'value'))],
            'developer_id' => ['nullable', 'integer', 'exists:developers,id'],
            'ref' => ['nullable', 'string', 'max:40', Rule::unique('properties', 'ref')->ignore($id)],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[\\p{L}\\p{N}-]+$/u', Rule::unique('properties', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'features' => ['nullable', 'string', 'max:3000'],
            'features_en' => ['nullable', 'string', 'max:3000'],
            'gallery' => ['nullable', 'string', 'max:3000'],
        ];
    }

    /** الملكية بتتفرض من السيرفر لغير الأدمن */
    protected function transform(array $data, ?Model $model): array
    {
        $data = parent::transform($data, $model);

        if (blank($data['slug'] ?? null)) {
            $data['slug'] = Property::buildSlug($data['title'], $data['title_en'] ?? null, $model ? (int) $model->getKey() : null);
        }

        return $this->applyOwner($data, $model);
    }

    /** الشركة بتشوف مشاريعها بس في قائمة الكمبوندات */
    private function compoundOptions(): array
    {
        $user = self::actor();

        return Compound::query()
            ->when(! self::actorSeesEverything(), fn ($q) => $q->where('owner_id', $user?->id ?? 0))
            ->orderBy('name')
            ->get()
            ->map(fn (Compound $c) => ['value' => (string) $c->id, 'label' => $c->name])
            ->all();
    }

    private function options(string $model, string $col): array
    {
        return $model::orderBy($col)->pluck($col, 'id')
            ->map(fn ($name, $id) => ['value' => (string) $id, 'label' => $name])
            ->values()->all();
    }
}
