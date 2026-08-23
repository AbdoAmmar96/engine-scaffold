<?php

namespace Modules\Pages\Http\Controllers;

use App\Support\ReservedSlugs;
use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Modules\Pages\Models\Page;

/**
 * إدارة صفحات المحتوى — سياسة الخصوصية والشروط وأي صفحة نصية تانية.
 *
 * نفس صيغة الكتابة بتاعة المدونة: سطر فاضي بين الفقرات، `## ` عنوان فرعي،
 * `- ` نقطة. صيغة واحدة يتعلّمها الأدمن مرة ويستخدمها في الاتنين.
 */
class PageAdminController extends ResourceController
{
    protected function modelClass(): string
    {
        return Page::class;
    }

    protected function key(): string
    {
        return 'pages';
    }

    protected function labels(): array
    {
        return ['plural' => 'الصفحات', 'singular' => 'صفحة'];
    }

    protected function searchable(): array
    {
        return ['title', 'title_en', 'slug'];
    }

    protected function columns(): array
    {
        return [
            'title' => 'الصفحة',
            'url' => 'الرابط',
            'is_indexable' => 'تظهر في جوجل',
            'is_active' => 'منشورة',
        ];
    }

    protected function listFilters(): array
    {
        return [[
            'name' => 'is_active',
            'label' => 'الحالة',
            'options' => [
                ['value' => '1', 'label' => 'منشورة'],
                ['value' => '0', 'label' => 'مسوّدة'],
            ],
        ]];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'العنوان (عربي)', 'type' => 'text', 'required' => true],
            ['name' => 'title_en', 'label' => 'العنوان (إنجليزي)', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'الرابط', 'type' => 'text',
                'hint' => 'اتركه فارغًا وسيتولّد من العنوان · تُفتح الصفحة على ‎/ar/‎ + الرابط'],
            ['name' => 'excerpt', 'label' => 'سطر تحت العنوان (عربي)', 'type' => 'textarea',
                'hint' => 'يظهر تحت العنوان ويُستخدم كوصف في جوجل إن كان الوصف فارغًا'],
            ['name' => 'excerpt_en', 'label' => 'سطر تحت العنوان (إنجليزي)', 'type' => 'textarea'],
            ['name' => 'body', 'label' => 'المحتوى (عربي)', 'type' => 'textarea',
                'hint' => 'سطر فارغ بين كل فقرة والأخرى · السطر الذي يبدأ بـ ## يصبح عنوانًا فرعيًا · والذي يبدأ بـ - يصبح نقطة'],
            ['name' => 'body_en', 'label' => 'المحتوى (إنجليزي)', 'type' => 'textarea'],
            ['name' => 'meta_title', 'label' => 'عنوان جوجل (عربي)', 'type' => 'text',
                'hint' => 'اتركه فارغًا ليُستخدم عنوان الصفحة'],
            ['name' => 'meta_title_en', 'label' => 'عنوان جوجل (إنجليزي)', 'type' => 'text'],
            ['name' => 'meta_description', 'label' => 'وصف جوجل (عربي)', 'type' => 'textarea'],
            ['name' => 'meta_description_en', 'label' => 'وصف جوجل (إنجليزي)', 'type' => 'textarea'],
            ['name' => 'is_indexable', 'label' => 'تظهر في نتائج البحث', 'type' => 'toggle'],
            ['name' => 'sort', 'label' => 'الترتيب', 'type' => 'number'],
            ['name' => 'is_active', 'label' => 'منشورة', 'type' => 'toggle'],
        ];
    }

    protected function rules(?int $id): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'slug' => [
                'nullable', 'string', 'max:190',
                Rule::unique('pages', 'slug')->ignore($id),
                // الرفض هنا مش تزيّد: slug زي `properties` بيخلّي الصفحة
                // تتحفظ بنجاح وتفتح على حاجة تانية خالص
                function (string $attribute, mixed $value, callable $fail): void {
                    if (ReservedSlugs::taken((string) $value)) {
                        $fail('هذا الرابط محجوز لصفحة موجودة في الموقع — اختر رابطًا آخر.');
                    }
                },
            ],
            'body' => ['nullable', 'string', 'max:120000'],
            'body_en' => ['nullable', 'string', 'max:120000'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_description_en' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function transform(array $data, ?Model $model): array
    {
        if (blank($data['slug'] ?? null)) {
            /** @var Page|null $model */
            $data['slug'] = Page::buildSlug($data['title'], $data['title_en'] ?? null, $model?->id);
        }

        return $data;
    }

    protected function rowPayload(Model $row): array
    {
        /** @var Page $row */
        return [
            'id' => $row->id,
            'title' => $row->title,
            'url' => '/ar/'.$row->slug,
            'is_indexable' => (bool) $row->is_indexable,
            'is_active' => (bool) $row->is_active,
        ];
    }
}
