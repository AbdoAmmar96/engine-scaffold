<?php

namespace Modules\Core\Http\Controllers;

use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
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
            ['name' => 'url', 'label' => 'الرابط', 'type' => 'text', 'required' => true,
                'hint' => 'مسار داخلي زي /properties (اللغة بتتحط لوحدها)، أو رابط كامل يبدأ بـ https://'],
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

    protected function rowPayload(Model $row): array
    {
        return [
            'id' => $row->id,
            'label' => $row->label,
            'url' => $row->url,
            'location_label' => MenuItem::LOCATIONS[$row->location] ?? $row->location,
            'sort' => $row->sort,
            'is_active' => (bool) $row->is_active,
        ];
    }
}
