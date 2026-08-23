<?php

namespace Modules\Reviews\Http\Controllers;

use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Modules\Compounds\Models\Compound;
use Modules\Properties\Models\Property;
use Modules\Reviews\Models\Review;

/**
 * إدارة آراء العملاء.
 *
 * مفيش رأي بيظهر على الموقع قبل ما يتعتمد — نفس دورة الوحدات. والعمود
 * «المصدر» ظاهر في الجدول عن قصد: الأدمن لازم يفرّق بنظرة بين رأي عميل
 * كتبه بنفسه ورأي هو كتبه بالإيد، وإلا الاتنين بيتلخبطوا بعد شهر.
 */
class ReviewAdminController extends ResourceController
{
    protected function modelClass(): string
    {
        return Review::class;
    }

    protected function key(): string
    {
        return 'reviews';
    }

    protected function labels(): array
    {
        return ['plural' => 'آراء العملاء', 'singular' => 'رأي'];
    }

    protected function searchable(): array
    {
        return ['author', 'author_en', 'body', 'body_en'];
    }

    protected function with(): array
    {
        return ['property', 'compound'];
    }

    protected function columns(): array
    {
        return [
            'author' => 'صاحب الرأي',
            'rating' => 'التقييم',
            'about' => 'عن',
            'source' => 'المصدر',
            'status' => 'الحالة',
        ];
    }

    protected function listFilters(): array
    {
        return [
            [
                'name' => 'status',
                'label' => 'الحالة',
                'options' => collect(Review::STATUSES)
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all(),
            ],
            [
                'name' => 'source',
                'label' => 'المصدر',
                'options' => collect(Review::SOURCES)
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all(),
            ],
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'author', 'label' => 'الاسم (عربي)', 'type' => 'text', 'required' => true],
            ['name' => 'author_en', 'label' => 'الاسم (إنجليزي)', 'type' => 'text'],
            ['name' => 'role', 'label' => 'السياق (عربي)', 'type' => 'text',
                'hint' => 'مثال: اشترى في كمبوند كذا — يمنح الرأي وزنًا'],
            ['name' => 'role_en', 'label' => 'السياق (إنجليزي)', 'type' => 'text'],
            ['name' => 'body', 'label' => 'الرأي (عربي)', 'type' => 'textarea', 'required' => true],
            ['name' => 'body_en', 'label' => 'الرأي (إنجليزي)', 'type' => 'textarea'],
            ['name' => 'rating', 'label' => 'التقييم (١–٥)', 'type' => 'number'],
            ['name' => 'avatar', 'label' => 'صورة صاحب الرأي', 'type' => 'image'],
            ['name' => 'property_id', 'label' => 'عن وحدة', 'type' => 'select', 'options' => $this->propertyOptions()],
            ['name' => 'compound_id', 'label' => 'عن كمبوند', 'type' => 'select', 'options' => $this->compoundOptions()],
            ['name' => 'source', 'label' => 'المصدر', 'type' => 'select',
                'options' => collect(Review::SOURCES)->map(fn ($l, $v) => ['value' => $v, 'label' => $l])->values()->all(),
                'hint' => 'ليكن صادقًا — هذا ما يفرّق بين رأي عميل ورأي مكتوب يدويًا'],
            ['name' => 'status', 'label' => 'الحالة', 'type' => 'select',
                'options' => collect(Review::STATUSES)->map(fn ($l, $v) => ['value' => $v, 'label' => $l])->values()->all()],
            ['name' => 'sort', 'label' => 'الترتيب', 'type' => 'number'],
        ];
    }

    protected function rules(?int $id): array
    {
        return [
            'author' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:4000'],
            'body_en' => ['nullable', 'string', 'max:4000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'source' => ['nullable', Rule::in(array_keys(Review::SOURCES))],
            'status' => ['nullable', Rule::in(array_keys(Review::STATUSES))],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'compound_id' => ['nullable', 'integer', 'exists:compounds,id'],
        ];
    }

    protected function transform(array $data, ?Model $model): array
    {
        $data['rating'] = min(5, max(1, (int) ($data['rating'] ?? 5)));

        // تاريخ النشر بيتحط ساعة الاعتماد مش ساعة الكتابة — الترتيب
        // على الموقع بيبقى بترتيب الاعتماد
        /** @var Review|null $model */
        if (($data['status'] ?? null) === 'published' && ! $model?->published_at) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function rowPayload(Model $row): array
    {
        /** @var Review $row */
        return [
            'id' => $row->id,
            'author' => $row->author,
            'rating' => str_repeat('★', $row->rating).str_repeat('☆', 5 - $row->rating),
            'about' => $row->about(),
            'source' => $row->sourceLabel(),
            'status' => $row->statusLabel(),
        ];
    }

    /** @return list<array{value: string, label: string}> */
    private function propertyOptions(): array
    {
        return Property::query()->orderByDesc('id')->limit(200)->get()
            ->map(fn (Property $p) => ['value' => (string) $p->id, 'label' => $p->title])->all();
    }

    /** @return list<array{value: string, label: string}> */
    private function compoundOptions(): array
    {
        return Compound::query()->orderBy('name')->get()
            ->map(fn (Compound $c) => ['value' => (string) $c->id, 'label' => $c->name])->all();
    }
}
