<?php

namespace Modules\Developers\Http\Controllers;

use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
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
        return [
            'name' => 'المطوّر',
            'founded_year' => 'التأسيس',
            'headquarters' => 'المقر',
            'sort' => 'الترتيب',
            'is_active' => 'مفعّل',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name',      'label' => 'الاسم (عربي)',    'type' => 'text', 'required' => true],
            ['name' => 'name_en',   'label' => 'الاسم (إنجليزي)', 'type' => 'text'],
            ['name' => 'slug',      'label' => 'رابط الصفحة',     'type' => 'text',
                'hint' => 'سيبه فاضي يتولّد من الاسم — /ar/developers/<الرابط>'],
            ['name' => 'about',     'label' => 'نبذة (عربي)',     'type' => 'textarea', 'hint' => 'فقرة في كل سطر — بتتعرض في صفحة المطوّر'],
            ['name' => 'about_en',  'label' => 'نبذة (إنجليزي)',  'type' => 'textarea'],
            ['name' => 'logo',      'label' => 'اللوجو',          'type' => 'image'],
            ['name' => 'cover',     'label' => 'غلاف الصفحة',     'type' => 'image', 'hint' => 'خلفية هيرو صفحة المطوّر'],
            ['name' => 'website',   'label' => 'الموقع الرسمي',   'type' => 'text', 'hint' => 'https://…'],
            ['name' => 'founded_year',    'label' => 'سنة التأسيس',      'type' => 'text', 'hint' => 'مثال: 2005'],
            ['name' => 'headquarters',    'label' => 'المقر (عربي)',     'type' => 'text'],
            ['name' => 'headquarters_en', 'label' => 'المقر (إنجليزي)',  'type' => 'text'],
            ['name' => 'sort',      'label' => 'الترتيب',         'type' => 'number'],
            ['name' => 'is_active', 'label' => 'مفعّل',           'type' => 'toggle'],
        ];
    }

    protected function rules(?int $id): array
    {
        return [
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[\p{L}\p{N}-]+$/u', Rule::unique('developers', 'slug')->ignore($id)],
            'about' => ['nullable', 'string', 'max:8000'],
            'about_en' => ['nullable', 'string', 'max:8000'],
            'website' => ['nullable', 'url', 'max:190'],
            'founded_year' => ['nullable', 'string', 'max:10'],
        ];
    }

    protected function transform(array $data, ?Model $model): array
    {
        if (blank($data['slug'] ?? null)) {
            $data['slug'] = Developer::buildSlug($data['name'], $data['name_en'] ?? null, $model ? (int) $model->getKey() : null);
        }

        return $data;
    }

    /** المطوّر اللي عليه مشاريع مايتمسحش — المشاريع هتفضل بلا مطوّر */
    protected function guardDelete(Model $model): ?string
    {
        /** @var Developer $model */
        $projects = $model->compounds()->count();

        return $projects > 0
            ? "المطوّر ده عليه {$projects} مشروع — انقلهم لمطوّر تاني الأول."
            : null;
    }
}
