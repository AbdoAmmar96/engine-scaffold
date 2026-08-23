<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Models\Activity;

/**
 * سجل النشاط — قراءة بس.
 *
 * مفيش تعديل ولا حذف عن قصد: سجل بيتعدّل مش سجل. الشاشة دي مش
 * ResourceController للسبب ده — الكيت بيفترض CRUD كامل.
 */
class ActivityController extends Controller
{
    public function index(Request $request): Response
    {
        $rows = Activity::query()
            ->with('user')
            ->when($request->query('action'), fn ($q, $a) => $q->where('action', $a))
            // بنطابق آخر جزء من اسم الكلاس من غير باك سلاش: السلاش
            // بيبقى حرف هروب في LIKE على MySQL ومش بيبقى على SQLite،
            // فاستخدامه كان هيخلي الفلتر يشتغل على واحدة ويقع على التانية
            ->when($request->query('subject'), fn ($q, $s) => $q->where('subject_type', 'like', '%'.$s))
            ->when(trim((string) $request->query('q')), fn ($q, $term) => $q->where(
                fn ($sub) => $sub->where('user_label', 'like', "%{$term}%")
                    ->orWhere('subject_label', 'like', "%{$term}%"),
            ))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $rows->getCollection()->transform(fn (Activity $row) => [
            'id' => $row->id,
            'user' => $row->user_label,
            'action' => Activity::ACTIONS[$row->action] ?? ['label' => $row->action, 'tone' => 'muted'],
            'subject' => $row->subjectLabel(),
            'label' => $row->subject_label ?: '—',
            'changed' => $row->changed ?? [],
            'ip' => $row->ip ?? '',
            'at' => $row->created_at?->format('Y/m/d H:i'),
        ]);

        return Inertia::render('Admin/Activity/Index', [
            'rows' => $rows,
            'filters' => [
                'action' => (string) $request->query('action', ''),
                'subject' => (string) $request->query('subject', ''),
                'q' => (string) $request->query('q', ''),
            ],
            'options' => [
                'actions' => collect(Activity::ACTIONS)
                    ->map(fn (array $a, string $key) => ['value' => $key, 'label' => $a['label']])
                    ->values()->all(),
                'subjects' => collect(Activity::SUBJECTS)
                    ->map(fn (string $label, string $key) => ['value' => $key, 'label' => $label])
                    ->values()->all(),
            ],
        ]);
    }
}
