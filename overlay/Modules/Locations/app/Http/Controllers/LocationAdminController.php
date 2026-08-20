<?php

namespace Modules\Locations\Http\Controllers;

use App\Support\ResourceController;
use Modules\Locations\Models\Location;

class LocationAdminController extends ResourceController
{
    protected function modelClass(): string { return Location::class; }
    protected function key(): string { return 'locations'; }

    protected function labels(): array
    {
        return ['plural' => 'المناطق', 'singular' => 'منطقة'];
    }

    protected function searchable(): array { return ['name', 'name_en']; }

    protected function columns(): array
    {
        return ['name' => 'المنطقة', 'note' => 'الوصف', 'sort' => 'الترتيب', 'is_active' => 'مفعّلة'];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name',      'label' => 'الاسم (عربي)',      'type' => 'text',   'required' => true],
            ['name' => 'name_en',   'label' => 'الاسم (إنجليزي)',   'type' => 'text'],
            ['name' => 'note',      'label' => 'وصف مختصر (عربي)',  'type' => 'text',   'hint' => 'مثال: التجمع الخامس · الرحاب · مدينتي'],
            ['name' => 'note_en',   'label' => 'وصف مختصر (إنجليزي)', 'type' => 'text'],
            ['name' => 'image',     'label' => 'الصورة',            'type' => 'image',  'hint' => 'مسار داخل public — مثال: /images/demo/area-1.jpg'],
            ['name' => 'sort',      'label' => 'الترتيب',           'type' => 'number'],
            ['name' => 'is_active', 'label' => 'مفعّلة',            'type' => 'toggle'],
        ];
    }
}
