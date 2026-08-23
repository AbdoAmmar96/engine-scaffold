<?php

namespace Modules\Marketing\Http\Controllers;

use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Compounds\Models\Compound;
use Modules\Marketing\Models\FeaturedAd;
use Modules\Properties\Models\Property;

/**
 * إدارة المساحات الإعلانية.
 *
 * التسويق والأدمن. الطلبات الجاية من المعلنين بتوصل هنا بحالة
 * «في انتظار الموافقة» — فالشاشة دي هي صندوق المراجعة كمان،
 * مش مكان الإنشاء بس.
 */
class FeaturedAdAdminController extends ResourceController
{
    protected function modelClass(): string
    {
        return FeaturedAd::class;
    }

    protected function key(): string
    {
        return 'featured-ads';
    }

    protected function labels(): array
    {
        return ['plural' => 'المساحات الإعلانية', 'singular' => 'مساحة إعلانية'];
    }

    protected function with(): array
    {
        return ['property', 'compound', 'requester'];
    }

    protected function searchable(): array
    {
        return ['position'];
    }

    protected function orderColumn(): ?string
    {
        return null;
    }

    protected function columns(): array
    {
        return [
            'position_label' => 'الموضع',
            'subject' => 'الإعلان على',
            'window' => 'الفترة',
            'state' => 'الحالة',
            'impressions' => 'ظهور',
            'clicks' => 'ضغطات',
            'ctr' => 'نسبة الضغط',
            'priority' => 'الأولوية',
        ];
    }

    /** صندوق المراجعة: /admin/featured-ads?status=pending */
    protected function listFilters(): array
    {
        return [[
            'name' => 'status',
            'label' => 'الحالة',
            'options' => collect(FeaturedAd::STATUSES)
                ->map(fn (array $s, string $key) => ['value' => $key, 'label' => $s['label']])
                ->values()->all(),
        ], [
            'name' => 'position',
            'label' => 'الموضع',
            'options' => collect(FeaturedAd::POSITIONS)
                ->map(fn (array $p, string $key) => ['value' => $key, 'label' => $p['label']])
                ->values()->all(),
        ]];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'position', 'label' => 'الموضع', 'type' => 'select', 'required' => true,
                'hint' => collect(FeaturedAd::POSITIONS)->map(fn ($p) => $p['label'].': '.$p['note'])->implode(' | '),
                'options' => collect(FeaturedAd::POSITIONS)
                    ->map(fn (array $p, string $key) => ['value' => $key, 'label' => $p['label']])
                    ->values()->all()],

            ['name' => 'property_id', 'label' => 'الوحدة', 'type' => 'select',
                'hint' => 'اختار وحدة أو مشروع — مش الاتنين',
                'options' => $this->propertyOptions()],
            ['name' => 'compound_id', 'label' => 'المشروع', 'type' => 'select',
                'options' => $this->compoundOptions()],

            ['name' => 'starts_at', 'label' => 'يبدأ في', 'type' => 'date',
                'hint' => 'سيبه فاضي يشتغل من دلوقتي'],
            ['name' => 'ends_at', 'label' => 'ينتهي في', 'type' => 'date',
                'hint' => 'سيبه فاضي يفضل شغّال لحد ما توقفه بإيدك'],

            ['name' => 'priority', 'label' => 'الأولوية', 'type' => 'number',
                'hint' => 'الأعلى بيتعرض الأول لما يكون فيه أكتر من إعلان في نفس الموضع'],

            ['name' => 'status', 'label' => 'الحالة', 'type' => 'select', 'required' => true,
                'options' => collect(FeaturedAd::STATUSES)
                    ->map(fn (array $s, string $key) => ['value' => $key, 'label' => $s['label']])
                    ->values()->all()],
            ['name' => 'rejection_reason', 'label' => 'سبب الرفض', 'type' => 'text',
                'hint' => 'بيتعرض لصاحب الطلب'],

            ['name' => 'is_active', 'label' => 'مفعّلة', 'type' => 'toggle',
                'hint' => 'إيقاف مؤقت من غير ما تغيّر التواريخ'],
        ];
    }

    protected function rules(?int $id): array
    {
        return [
            'position' => ['required', Rule::in(array_keys(FeaturedAd::POSITIONS))],
            'status' => ['required', Rule::in(array_keys(FeaturedAd::STATUSES))],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'compound_id' => ['nullable', 'integer', 'exists:compounds,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:999'],
            'rejection_reason' => ['nullable', 'string', 'max:400'],
        ];
    }

    /**
     * الإعلان لازم يكون على حاجة واحدة. من غير الفحص ده بيتحفظ على
     * الاتنين وبيتعرض بالوحدة ويودّي المشروع — أو بيتحفظ على ولا حاجة
     * وبيختفي من غير ما حد يفهم ليه.
     */
    protected function transform(array $data, ?Model $model): array
    {
        $property = filled($data['property_id'] ?? null);
        $compound = filled($data['compound_id'] ?? null);

        if ($property === $compound) {
            throw ValidationException::withMessages([
                'property_id' => $property
                    ? 'اختار وحدة أو مشروع — مش الاتنين.'
                    : 'لازم تختار وحدة أو مشروع.',
            ]);
        }

        if ($data['status'] !== 'rejected') {
            $data['rejection_reason'] = null;
        }

        return $data;
    }

    protected function rowPayload(Model $row): array
    {
        /** @var FeaturedAd $row */
        return [
            'id' => $row->id,
            'position_label' => FeaturedAd::POSITIONS[$row->position]['label'] ?? $row->position,
            'subject' => $row->subject('ar') ?: '—',
            'window' => $this->window($row),
            'state' => $row->stateLabel(),
            'impressions' => $row->impressions,
            'clicks' => $row->clicks,
            'ctr' => $row->ctr().'%',
            'priority' => $row->priority,
        ];
    }

    private function window(FeaturedAd $ad): string
    {
        $from = $ad->starts_at?->format('Y/m/d') ?: 'من دلوقتي';
        $to = $ad->ends_at?->format('Y/m/d') ?: 'مفتوح';

        return "{$from} ← {$to}";
    }

    /** الوحدات المعروضة بس — إعلان على وحدة مخفية مساحة ضايعة */
    private function propertyOptions(): array
    {
        return Property::published()
            ->orderByDesc('id')
            ->limit(300)
            ->get()
            ->map(fn (Property $p) => [
                'value' => (string) $p->id,
                'label' => trim($p->title.' — '.($p->ref ?? '')),
            ])
            ->all();
    }

    private function compoundOptions(): array
    {
        return Compound::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Compound $c) => ['value' => (string) $c->id, 'label' => $c->name])
            ->all();
    }
}
