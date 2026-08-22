<?php

namespace Modules\Core\Http\Controllers;

use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Modules\Core\Models\MenuItem;

/**
 * إدارة لينكات الهيدر والفوتر — الترتيب بيتحدد بحقل "الترتيب".
 */
class MenuAdminController extends ResourceController
{
    protected function modelClass(): string { return MenuItem::class; }
    protected function key(): string { return 'menus'; }

    protected function labels(): array
    {
        return ['plural' => 'القوائم', 'singular' => 'لينك'];
    }

    protected function searchable(): array { return ['label', 'label_en', 'url']; }

    protected function columns(): array
    {
        return [
            'label' => 'الاسم',
            'url' => 'الرابط',
            'parent_label' => 'تحت',
            'location_label' => 'المكان',
            'sort' => 'الترتيب',
            'is_active' => 'ظاهر',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'label', 'label' => 'الاسم (عربي)', 'type' => 'text', 'required' => true],
            ['name' => 'label_en', 'label' => 'الاسم (إنجليزي)', 'type' => 'text'],
            ['name' => 'url', 'label' => 'الرابط', 'type' => 'text',
                'hint' => 'مسار داخلي زي /properties (اللغة بتتحط لوحدها)، أو رابط كامل يبدأ بـ https://. سيبه فاضي لو ده عنوان قائمة منسدلة.'],
            ['name' => 'parent_id', 'label' => 'تحت لينك', 'type' => 'select',
                'hint' => 'سيبه فاضي للينك رئيسي — اختيار أب بيحطّه جوه قايمة منسدلة',
                'options' => $this->parentOptions()],
            ['name' => 'location', 'label' => 'المكان', 'type' => 'select', 'required' => true, 'options' => array_map(
                fn ($label, $value) => ['value' => $value, 'label' => $label],
                array_values(MenuItem::LOCATIONS),
                array_keys(MenuItem::LOCATIONS),
            )],
            ['name' => 'new_tab', 'label' => 'يفتح في تاب جديد', 'type' => 'toggle'],
            ['name' => 'sort', 'label' => 'الترتيب', 'type' => 'number', 'hint' => 'الأقل بيظهر الأول'],
            ['name' => 'is_active', 'label' => 'ظاهر', 'type' => 'toggle'],
        ];
    }

    protected function rules(?int $id): array
    {
        return [
            // مستوى واحد بس: الأب لازم يكون لينك رئيسي، ومحدش يبقى أب لنفسه
            'parent_id' => [
                'nullable', 'integer',
                Rule::exists('menu_items', 'id')->whereNull('parent_id'),
                Rule::notIn([$id]),
            ],
            'url' => ['nullable', 'string', 'max:190'],
        ];
    }

    /**
     * اللينكات اللي تنفع تكون أب: رئيسية بس (مستوى واحد)،
     * والعنصر نفسه مستثنى عشان مايختارش نفسه.
     */
    private function parentOptions(): array
    {
        $current = (int) request()->route('id');

        return MenuItem::whereNull('parent_id')
            ->when($current, fn ($q) => $q->where('id', '!=', $current))
            ->orderBy('location')->orderBy('sort')
            ->get()
            ->map(fn (MenuItem $i) => [
                'value' => (string) $i->id,
                'label' => (MenuItem::LOCATIONS[$i->location] ?? $i->location).' — '.$i->label,
            ])
            ->all();
    }

    protected function rowPayload(Model $row): array
    {
        /** @var MenuItem $row */
        return [
            'id' => $row->id,
            'label' => $row->label,
            'url' => $row->url ?: '—',
            'parent_label' => $row->parent_id ? (MenuItem::find($row->parent_id)?->label ?? '—') : '—',
            'location_label' => MenuItem::LOCATIONS[$row->location] ?? $row->location,
            'sort' => $row->sort,
            'is_active' => (bool) $row->is_active,
        ];
    }
}
