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
            'status' => 'الحالة',
            'is_active' => 'معروض',
        ]);
    }

    /** قايمة المراجعة: /admin/properties?status=pending */
    protected function listFilters(): array
    {
        return [[
            'name' => 'status',
            'label' => 'الحالة',
            'options' => collect(Property::STATUSES)
                ->map(fn (array $s, string $key) => ['value' => $key, 'label' => $s['label']])
                ->values()->all(),
        ]];
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
            ['name' => 'price_amount', 'label' => 'السعر (رقم)',      'type' => 'number',
                'hint' => 'الرقم من غير فواصل — الفلاتر والترتيب بيشتغلوا عليه'],
            ['name' => 'price',       'label' => 'نص السعر (عربي)',   'type' => 'text',
                'hint' => 'اختياري — بيغلب الرقم في العرض بس. مثال: السعر عند الاستعلام'],
            ['name' => 'price_en',    'label' => 'نص السعر (إنجليزي)', 'type' => 'text'],
            ['name' => 'down_payment', 'label' => 'المقدم (رقم)',      'type' => 'number'],
            ['name' => 'monthly_installment', 'label' => 'القسط الشهري (رقم)', 'type' => 'number'],
            ['name' => 'installment_years',   'label' => 'سنوات التقسيط',      'type' => 'number'],
            ['name' => 'delivery_year',       'label' => 'سنة التسليم',        'type' => 'number', 'hint' => 'مثال: 2028'],
            ['name' => 'beds',        'label' => 'غرف النوم',         'type' => 'number'],
            ['name' => 'baths',       'label' => 'الحمامات',          'type' => 'number'],
            ['name' => 'size',        'label' => 'المساحة (م²)',      'type' => 'number'],
            ['name' => 'finishing',   'label' => 'التشطيب',           'type' => 'select', 'options' => array_map(
                fn ($labels, $key) => ['value' => $key, 'label' => $labels['ar']],
                array_values(Property::FINISHING),
                array_keys(Property::FINISHING),
            )],
            ['name' => 'floor',       'label' => 'الدور',             'type' => 'text', 'hint' => 'مثال: الثالث · أرضي'],
            ['name' => 'has_garden',  'label' => 'حديقة',             'type' => 'toggle'],
            ['name' => 'has_roof',    'label' => 'روف',               'type' => 'toggle'],
            ['name' => 'has_dressing_room', 'label' => 'غرفة ملابس',  'type' => 'toggle'],
            ['name' => 'image',       'label' => 'الصورة الرئيسية',   'type' => 'image'],
            ['name' => 'gallery',     'label' => 'صور إضافية',        'type' => 'gallery'],
            ['name' => 'sort',        'label' => 'الترتيب',           'type' => 'number'],
            ['name' => 'is_active',   'label' => 'معروض على الموقع',  'type' => 'toggle',
                'hint' => 'إخفاء مؤقت من صاحب الوحدة — مستقل عن حالة المراجعة'],
            ...$this->moderationFields(),
        ];
    }

    /**
     * الاعتماد والتمييز صلاحيتين منفصلتين:
     *   «publish listings» ⇒ الحالة وسبب الرفض
     *   «feature listings» ⇒ الإعلان المميّز
     * فالتسويق يقدر يميّز من غير ما يقدر ينشر، ومدخل البيانات لا ده ولا ده.
     */
    private function moderationFields(): array
    {
        $fields = [];

        if (self::actorCan('publish listings')) {
            $fields[] = ['name' => 'status', 'label' => 'حالة المراجعة', 'type' => 'select', 'required' => true,
                'hint' => 'النشر بيولّد كود مرجعي للوحدة لو مالهاش واحد',
                'options' => collect(Property::STATUSES)
                    ->map(fn (array $s, string $key) => ['value' => $key, 'label' => $s['label']])
                    ->values()->all()];

            $fields[] = ['name' => 'rejection_reason', 'label' => 'سبب الرفض', 'type' => 'textarea',
                'hint' => 'بيتعرض لصاحب الوحدة عشان يعرف يصلّح إيه'];
        }

        if (self::actorCan('feature listings')) {
            $fields[] = ['name' => 'is_featured', 'label' => 'إعلان مميّز', 'type' => 'toggle',
                'hint' => 'بيتصدّر نتايج البحث'];
        }

        return $fields;
    }

    private static function actorCan(string $permission): bool
    {
        return (bool) self::actor()?->can($permission);
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
            'price_amount' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'down_payment' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'monthly_installment' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
            'installment_years' => ['nullable', 'integer', 'min:0', 'max:40'],
            'delivery_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'finishing' => ['nullable', Rule::in(array_keys(Property::FINISHING))],
            'floor' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', Rule::in(array_keys(Property::STATUSES))],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** الملكية ودورة الاعتماد بيتفرضوا من السيرفر لغير الأدمن */
    protected function transform(array $data, ?Model $model): array
    {
        $data = parent::transform($data, $model);

        if (blank($data['slug'] ?? null)) {
            $data['slug'] = Property::buildSlug($data['title'], $data['title_en'] ?? null, $model ? (int) $model->getKey() : null);
        }

        return $this->applyModeration($this->applyOwner($data, $model), $model);
    }

    /**
     * تلات طبقات، من الأعلى للأدنى:
     *
     *   معاه «publish listings» → بيحدد الحالة بإيده، مفيش قيود.
     *   معاه «manage catalog» بس (مدخل بيانات/تسويق) → بيعدّل البيانات،
     *     والحالة بتفضل زي ما هي، والجديد بيدخل المراجعة. من غير السطر ده
     *     كان يقدر ينشر بمجرد إنه يعمل وحدة جديدة — ديفولت الموديل «منشور».
     *   مالوش ولا واحدة (صاحب الوحدة) → أي تعديل بيرجّعها للمراجعة.
     */
    private function applyModeration(array $data, ?Model $model): array
    {
        if (! self::actorCan('feature listings')) {
            unset($data['is_featured']);
        }

        if (self::actorCan('publish listings')) {
            return $data;
        }

        /** @var Property|null $model */
        $current = $model?->status;

        if (self::actorSeesEverything()) {
            unset($data['rejection_reason']);
            $data['status'] = $current ?? 'pending';

            return $data;
        }

        $data['rejection_reason'] = null;

        // المباع/المؤجّر بيفضل زي ما هو — التعديل عليه مش إعادة عرض.
        // أي حالة تانية (جديدة أو منشورة أو مرفوضة) بترجع للمراجعة.
        $data['status'] = in_array($current, ['sold', 'rented'], true) ? $current : 'pending';

        return $data;
    }

    /** الحالة بتتعرض كشارة ملوّنة — شكل {label, tone} */
    protected function rowPayload(Model $row): array
    {
        /** @var Property $row */
        // array_replace مش + : الـ + بيسيب قيمة الشمال، فالنص الخام كان هيغلب الشارة
        return array_replace(parent::rowPayload($row), [
            'status' => Property::STATUSES[$row->status] ?? ['label' => $row->status, 'tone' => 'muted'],
        ]);
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
