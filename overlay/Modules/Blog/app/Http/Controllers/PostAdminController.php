<?php

namespace Modules\Blog\Http\Controllers;

use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Modules\Blog\Models\Post;

/**
 * إدارة المدونة — المقال بيتكتب بالعربي والإنجليزي في نفس الصفحة،
 * والرابط (slug) بيتولّد لوحده لو سِبته فاضي.
 */
class PostAdminController extends ResourceController
{
    protected function modelClass(): string
    {
        return Post::class;
    }

    protected function key(): string
    {
        return 'posts';
    }

    protected function labels(): array
    {
        return ['plural' => 'المدونة', 'singular' => 'مقال'];
    }

    protected function searchable(): array
    {
        return ['title', 'title_en', 'slug', 'category'];
    }

    protected function columns(): array
    {
        return [
            'title' => 'المقال',
            'category' => 'القسم',
            'author' => 'الكاتب',
            'published_at' => 'تاريخ النشر',
            'is_active' => 'منشور',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'العنوان (عربي)', 'type' => 'text', 'required' => true],
            ['name' => 'title_en', 'label' => 'العنوان (إنجليزي)', 'type' => 'text'],
            ['name' => 'slug', 'label' => 'الرابط', 'type' => 'text',
                'hint' => 'سيبه فاضي وهيتولّد من العنوان'],
            ['name' => 'category', 'label' => 'القسم (عربي)', 'type' => 'text', 'hint' => 'مثال: دليل المشتري'],
            ['name' => 'category_en', 'label' => 'القسم (إنجليزي)', 'type' => 'text'],
            ['name' => 'author', 'label' => 'الكاتب', 'type' => 'text'],
            ['name' => 'published_at', 'label' => 'تاريخ النشر', 'type' => 'date'],
            ['name' => 'image', 'label' => 'صورة الغلاف', 'type' => 'image'],
            ['name' => 'excerpt', 'label' => 'المقدمة (عربي)', 'type' => 'textarea',
                'hint' => 'سطرين بيظهروا في كارت المقال'],
            ['name' => 'excerpt_en', 'label' => 'المقدمة (إنجليزي)', 'type' => 'textarea'],
            ['name' => 'body', 'label' => 'المحتوى (عربي)', 'type' => 'textarea',
                'hint' => 'سطر فاضي بين كل فقرة والتانية · السطر اللي بيبدأ بـ ## بيبقى عنوان فرعي'],
            ['name' => 'body_en', 'label' => 'المحتوى (إنجليزي)', 'type' => 'textarea'],
            ['name' => 'sort', 'label' => 'الترتيب', 'type' => 'number'],
            ['name' => 'is_active', 'label' => 'منشور', 'type' => 'toggle'],
        ];
    }

    protected function rules(?int $id): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('posts', 'slug')->ignore($id)],
            'published_at' => ['nullable', 'date'],
            'body' => ['nullable', 'string', 'max:60000'],
            'body_en' => ['nullable', 'string', 'max:60000'],
        ];
    }

    protected function transform(array $data, ?Model $model): array
    {
        if (blank($data['slug'] ?? null)) {
            $data['slug'] = Post::buildSlug($data['title'], $data['title_en'] ?? null, $model?->id);
        }

        $data['published_at'] ??= now()->toDateString();

        return $data;
    }

    protected function rowPayload(Model $row): array
    {
        return [
            'id' => $row->id,
            'title' => $row->title,
            'category' => $row->category ?: '—',
            'author' => $row->author ?: '—',
            'published_at' => $row->published_at?->format('Y/m/d') ?? '—',
            'is_active' => (bool) $row->is_active,
        ];
    }

    protected function itemPayload(Model $model): array
    {
        return parent::itemPayload($model) + [
            'published_at' => $model->published_at?->toDateString(),
        ];
    }
}
