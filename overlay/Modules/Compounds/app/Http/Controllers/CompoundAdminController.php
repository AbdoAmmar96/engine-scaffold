<?php

namespace Modules\Compounds\Http\Controllers;

use App\Support\OwnedResource;
use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Modules\Compounds\Models\Compound;
use Modules\Developers\Models\Developer;
use Modules\Locations\Models\Location;

class CompoundAdminController extends ResourceController
{
    use OwnedResource;

    protected function modelClass(): string
    {
        return Compound::class;
    }

    protected function key(): string
    {
        return 'compounds';
    }

    protected function labels(): array
    {
        return ['plural' => 'الكمبوندات', 'singular' => 'كمبوند'];
    }

    protected function searchable(): array
    {
        return ['name', 'name_en'];
    }

    protected function with(): array
    {
        return ['developer', 'location', 'owner'];
    }

    protected function columns(): array
    {
        return array_filter([
            'name' => 'الكمبوند',
            'developer.name' => 'المطوّر',
            'location.name' => 'المنطقة',
            'owner.name' => self::actorSeesEverything() ? 'الحساب المالك' : null,
            'starting_price' => 'يبدأ من',
            'delivery' => 'التسليم',
            'is_active' => 'مفعّل',
        ]);
    }

    protected function fields(): array
    {
        return [
            ...$this->ownerField('الشركة المالكة'),
            ['name' => 'name',           'label' => 'الاسم (عربي)',      'type' => 'text', 'required' => true],
            ['name' => 'name_en',        'label' => 'الاسم (إنجليزي)',   'type' => 'text'],
            ['name' => 'slug',           'label' => 'رابط الصفحة',       'type' => 'text', 'hint' => 'سيبه فاضي يتولّد من الاسم — /ar/compounds/<الرابط>'],
            ['name' => 'developer_id',   'label' => 'المطوّر',           'type' => 'select', 'options' => $this->options(Developer::class)],
            ['name' => 'location_id',    'label' => 'المنطقة',           'type' => 'select', 'options' => $this->options(Location::class)],
            ['name' => 'description',    'label' => 'الوصف (عربي)',      'type' => 'textarea'],
            ['name' => 'description_en', 'label' => 'الوصف (إنجليزي)',   'type' => 'textarea'],
            ['name' => 'features',       'label' => 'المميزات (عربي)',   'type' => 'textarea', 'hint' => 'ميزة في كل سطر'],
            ['name' => 'features_en',    'label' => 'المميزات (إنجليزي)', 'type' => 'textarea', 'hint' => 'ميزة في كل سطر'],
            ['name' => 'starting_price', 'label' => 'يبدأ من',           'type' => 'text', 'hint' => 'مثال: EGP 5,400,000'],
            ['name' => 'down_payment',   'label' => 'المقدم',            'type' => 'text', 'hint' => 'مثال: 5%'],
            ['name' => 'installment_years',    'label' => 'التقسيط (عربي)',    'type' => 'text', 'hint' => 'مثال: 8 سنوات'],
            ['name' => 'installment_years_en', 'label' => 'التقسيط (إنجليزي)', 'type' => 'text'],
            ['name' => 'delivery',       'label' => 'التسليم',           'type' => 'text', 'hint' => 'مثال: Q4 2027'],
            ['name' => 'image',          'label' => 'الصورة الرئيسية',   'type' => 'image'],
            ['name' => 'gallery',        'label' => 'صور إضافية',        'type' => 'gallery'],
            ['name' => 'is_new',         'label' => 'إطلاق جديد',        'type' => 'toggle'],
            ['name' => 'sort',           'label' => 'الترتيب',           'type' => 'number'],
            ['name' => 'is_active',      'label' => 'مفعّل',             'type' => 'toggle'],
        ];
    }

    protected function rules(?int $id): array
    {
        return $this->ownerRules() + [
            // الـ id بيوصل من الفورم، فلازم يتأكد إنه موجود فعلًا
            'developer_id' => ['nullable', 'integer', 'exists:developers,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[\\p{L}\\p{N}-]+$/u', Rule::unique('compounds', 'slug')->ignore($id)],
            'features' => ['nullable', 'string', 'max:3000'],
            'features_en' => ['nullable', 'string', 'max:3000'],
            'gallery' => ['nullable', 'string', 'max:3000'],
        ];
    }

    /** الرابط بيتولّد من الاسم، والملكية بتتفرض من السيرفر لغير الأدمن */
    protected function transform(array $data, ?Model $model): array
    {
        if (blank($data['slug'] ?? null)) {
            $data['slug'] = Compound::buildSlug($data['name'], $data['name_en'] ?? null, $model ? (int) $model->getKey() : null);
        }

        return $this->applyOwner($data, $model);
    }

    /**
     * المشروع اللي عليه وحدات مايتمسحش — الوحدات هتفضل معلّقة على مشروع مش موجود.
     */
    protected function guardDelete(Model $model): ?string
    {
        /** @var Compound $model */
        $units = $model->properties()->count();

        return $units > 0
            ? "المشروع ده عليه {$units} وحدة — انقلهم أو امسحهم الأول."
            : null;
    }

    private function options(string $model): array
    {
        return $model::orderBy('name')->pluck('name', 'id')
            ->map(fn ($name, $id) => ['value' => (string) $id, 'label' => $name])
            ->values()->all();
    }
}
