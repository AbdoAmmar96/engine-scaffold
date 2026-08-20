<?php

namespace Modules\Developers\Http\Controllers;

use App\Support\ResourceController;
use Modules\Developers\Models\Developer;

class DeveloperAdminController extends ResourceController
{
    protected function modelClass(): string { return Developer::class; }
    protected function key(): string { return 'developers'; }

    protected function labels(): array
    {
        return ['plural' => 'المطوّرون', 'singular' => 'مطوّر'];
    }

    protected function searchable(): array { return ['name', 'name_en']; }

    protected function columns(): array
    {
        return ['name' => 'المطوّر', 'about' => 'نبذة', 'sort' => 'الترتيب', 'is_active' => 'مفعّل'];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name',      'label' => 'الاسم (عربي)',    'type' => 'text', 'required' => true],
            ['name' => 'name_en',   'label' => 'الاسم (إنجليزي)', 'type' => 'text'],
            ['name' => 'about',     'label' => 'نبذة (عربي)',     'type' => 'textarea'],
            ['name' => 'about_en',  'label' => 'نبذة (إنجليزي)',  'type' => 'textarea'],
            ['name' => 'logo',      'label' => 'اللوجو',          'type' => 'image'],
            ['name' => 'sort',      'label' => 'الترتيب',         'type' => 'number'],
            ['name' => 'is_active', 'label' => 'مفعّل',           'type' => 'toggle'],
        ];
    }
}
