<?php

namespace Modules\Seo\Http\Controllers;

use App\Support\ResourceController;
use App\Support\SharedSlugSpace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;
use Modules\Seo\Models\LandingPage;

/**
 * تحرير صفحات الهبوط البرمجية.
 *
 * الصفحات نفسها بيولّدها `php artisan seo:landing-pages` من الوحدات
 * الموجودة. الشاشة دي للنصوص: العنوان والمقدمة والميتا. سيب أي حقل
 * فاضي والنص المولّد بيرجع مكانه — فمفيش صفحة بتفضل بلا عنوان.
 */
class LandingPageAdminController extends ResourceController
{
    protected function modelClass(): string
    {
        return LandingPage::class;
    }

    protected function key(): string
    {
        return 'landing-pages';
    }

    protected function labels(): array
    {
        return ['plural' => 'صفحات الهبوط', 'singular' => 'صفحة هبوط'];
    }

    protected function searchable(): array
    {
        return ['slug', 'h1', 'h1_en'];
    }

    protected function with(): array
    {
        return ['location'];
    }

    protected function columns(): array
    {
        return [
            'h1' => 'العنوان',
            'slug' => 'الرابط',
            'units_count' => 'الوحدات',
            'is_active' => 'مفعّلة',
        ];
    }

    protected function listFilters(): array
    {
        return [[
            'name' => 'purpose',
            'label' => 'الغرض',
            'options' => [
                ['value' => 'sale', 'label' => 'بيع'],
                ['value' => 'rent', 'label' => 'إيجار'],
            ],
        ]];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'type', 'label' => 'النوع', 'type' => 'select', 'options' => array_map(
                fn ($t) => ['value' => $t, 'label' => $t],
                array_keys(Property::TYPE_PLURALS),
            ), 'hint' => 'سيبه فاضي لصفحة منطقة (كل الأنواع)'],
            ['name' => 'purpose', 'label' => 'الغرض', 'type' => 'select', 'options' => [
                ['value' => 'sale', 'label' => 'بيع'],
                ['value' => 'rent', 'label' => 'إيجار'],
            ]],
            ['name' => 'location_id', 'label' => 'المنطقة', 'type' => 'select',
                'options' => Location::orderBy('name')->get()->map(fn (Location $l) => [
                    'value' => (string) $l->id,
                    'label' => $l->name,
                ])->all()],
            ['name' => 'slug', 'label' => 'الرابط', 'type' => 'text',
                'hint' => 'سيبه فاضي يتولّد من التركيبة — /ar/properties/<الرابط>. غيّره وأنت واخد بالك: الرابط القديم بيبقى 404.'],

            ['name' => 'h1', 'label' => 'العنوان (عربي)', 'type' => 'text', 'hint' => 'فاضي = يتولّد من التركيبة'],
            ['name' => 'h1_en', 'label' => 'العنوان (إنجليزي)', 'type' => 'text'],
            ['name' => 'intro', 'label' => 'المقدمة (عربي)', 'type' => 'textarea', 'hint' => 'الفقرة اللي تحت العنوان'],
            ['name' => 'intro_en', 'label' => 'المقدمة (إنجليزي)', 'type' => 'textarea'],
            ['name' => 'meta_title', 'label' => 'عنوان الميتا (عربي)', 'type' => 'text', 'hint' => 'فاضي = نفس العنوان'],
            ['name' => 'meta_title_en', 'label' => 'عنوان الميتا (إنجليزي)', 'type' => 'text'],
            ['name' => 'meta_description', 'label' => 'وصف الميتا (عربي)', 'type' => 'textarea', 'hint' => 'جوجل بيعرض أول ١٦٠ حرف تقريبًا'],
            ['name' => 'meta_description_en', 'label' => 'وصف الميتا (إنجليزي)', 'type' => 'textarea'],

            ['name' => 'sort', 'label' => 'الترتيب', 'type' => 'number'],
            ['name' => 'is_active', 'label' => 'مفعّلة', 'type' => 'toggle',
                'hint' => 'المقفولة بترجع 404 وبتختفي من خريطة الموقع'],
        ];
    }

    protected function rules(?int $id): array
    {
        return [
            'type' => ['nullable', Rule::in(array_keys(Property::TYPE_PLURALS))],
            'purpose' => ['nullable', Rule::in(array_keys(LandingPage::PURPOSES))],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[\p{L}\p{N}-]+$/u', function (string $attribute, mixed $value, callable $fail) use ($id) {
                // التفرّد على الوحدات وصفحات الهبوط سوا — بيتشاركوا /properties/
                if (SharedSlugSpace::taken((string) $value, 'seo_landing_pages', $id)) {
                    $fail('الرابط ده مستخدم في وحدة أو صفحة تانية.');
                }
            }],
            'intro' => ['nullable', 'string', 'max:5000'],
            'intro_en' => ['nullable', 'string', 'max:5000'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_description_en' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function transform(array $data, ?Model $model): array
    {
        $this->guardCombo($data, $model);

        if (blank($data['slug'] ?? null)) {
            $location = filled($data['location_id'] ?? null) ? Location::find($data['location_id']) : null;

            $data['slug'] = LandingPage::buildSlug(
                LandingPage::slugFor($data['type'] ?? null, $data['purpose'] ?? null, $location),
                null,
                $model ? (int) $model->getKey() : null,
            );
        }

        return $data;
    }

    /**
     * التركيبة مفتاح فريد في الجدول. من غير الفحص ده الحفظ بيرمي
     * خطأ قاعدة بيانات خام في وش المستخدم بدل رسالة يفهمها.
     */
    private function guardCombo(array $data, ?Model $model): void
    {
        $exists = LandingPage::query()
            ->where('type', $data['type'] ?? null)
            ->where('purpose', $data['purpose'] ?? null)
            ->where('location_id', $data['location_id'] ?? null)
            ->when($model, fn ($q) => $q->where('id', '!=', $model->getKey()))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'type' => 'فيه صفحة بنفس التركيبة (نوع + غرض + منطقة) موجودة أصلًا.',
            ]);
        }
    }

    /** العنوان في الجدول: المكتوب، وإلا المولّد — نفس اللي الزائر بيشوفه */
    protected function rowPayload(Model $row): array
    {
        /** @var LandingPage $row */
        return array_replace(parent::rowPayload($row), [
            'h1' => $row->heading('ar'),
        ]);
    }
}
