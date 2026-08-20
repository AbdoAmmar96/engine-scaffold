<?php

namespace Modules\Leads\Http\Controllers;

use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Modules\Leads\Models\Lead;

/**
 * صندوق وارد الطلبات — كل ليد جاي من فورم الموقع بيظهر هنا،
 * وبتحرّك حالته من "جديد" لحد "تمت الصفقة" وتكتب ملاحظات المتابعة.
 */
class LeadAdminController extends ResourceController
{
    protected function modelClass(): string { return Lead::class; }
    protected function key(): string { return 'leads'; }

    protected function labels(): array
    {
        return ['plural' => 'الطلبات', 'singular' => 'طلب'];
    }

    protected function searchable(): array { return ['name', 'phone', 'email', 'message']; }

    /** جدول leads مفيهوش عمود sort — الأحدث الأول */
    protected function orderColumn(): ?string { return null; }

    protected function columns(): array
    {
        return [
            'name' => 'الاسم',
            'phone' => 'الموبايل',
            'area' => 'المنطقة',
            'budget' => 'الميزانية',
            'source_label' => 'المصدر',
            'status' => 'الحالة',
            'received' => 'وصل في',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
            ['name' => 'phone', 'label' => 'الموبايل', 'type' => 'text', 'required' => true],
            ['name' => 'email', 'label' => 'الإيميل', 'type' => 'text'],
            ['name' => 'area', 'label' => 'المنطقة المطلوبة', 'type' => 'text'],
            ['name' => 'budget', 'label' => 'الميزانية', 'type' => 'text'],
            ['name' => 'status', 'label' => 'الحالة', 'type' => 'select', 'required' => true, 'options' => array_map(
                fn ($v, $k) => ['value' => $k, 'label' => $v['label']],
                array_values(Lead::STATUSES),
                array_keys(Lead::STATUSES),
            )],
            ['name' => 'source', 'label' => 'المصدر', 'type' => 'select', 'options' => array_map(
                fn ($v, $k) => ['value' => $k, 'label' => $v],
                array_values(Lead::SOURCES),
                array_keys(Lead::SOURCES),
            )],
            ['name' => 'message', 'label' => 'رسالة العميل', 'type' => 'textarea',
                'hint' => 'اللي كتبه العميل في الفورم'],
            ['name' => 'notes', 'label' => 'ملاحظات المتابعة', 'type' => 'textarea',
                'hint' => 'للفريق بس — مش بيظهر للعميل'],
        ];
    }

    protected function rowPayload(Model $row): array
    {
        return [
            'id' => $row->id,
            'name' => $row->name,
            'phone' => $row->phone,
            'area' => $row->area ?: '—',
            'budget' => $row->budget ?: '—',
            'source_label' => Lead::SOURCES[$row->source] ?? $row->source,
            // شكل {label, tone} بيتحوّل لشارة ملوّنة في الجدول
            'status' => Lead::STATUSES[$row->status] ?? ['label' => $row->status, 'tone' => 'muted'],
            'received' => $row->created_at?->format('Y/m/d — H:i'),
        ];
    }
}
