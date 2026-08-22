<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * كنترولر CRUD عام للأدمن — كل موديول بيورّثه ويحدّد الموديل والحقول بس.
 * الشاشتين (Index / Form) في React عامّتين وبيتبنوا من الـ schema اللي هنا،
 * فإضافة موديول جديد = كلاس صغير + راوت، من غير أي صفحة جديدة.
 *
 * أنواع الحقول المدعومة: text · number · password · date · textarea · select · toggle · image · gallery
 */
abstract class ResourceController extends Controller
{
    /** الموديل اللي الريسورس شغال عليه */
    abstract protected function modelClass(): string;

    /** مفتاح الراوت: admin.<key>.index */
    abstract protected function key(): string;

    /** العنوان في الشاشة (جمع ومفرد) */
    abstract protected function labels(): array;

    /** أعمدة الجدول: ['key' => 'label'] */
    abstract protected function columns(): array;

    /** حقول الفورم */
    abstract protected function fields(): array;

    /**
     * فلاتر منسدلة فوق الجدول:
     * [['name' => 'status', 'label' => 'الحالة', 'options' => [['value'=>..,'label'=>..]]]]
     * القيمة بتتطبّق كـ where على نفس العمود، وأي قيمة مش في options بتتجاهل.
     */
    protected function listFilters(): array
    {
        return [];
    }

    /** الأعمدة اللي البحث بيدوّر فيها */
    protected function searchable(): array
    {
        return ['name'];
    }

    /** علاقات بتتحمّل مع القائمة */
    protected function with(): array
    {
        return [];
    }

    /** عمود الترتيب — null لو الجدول مفيهوش واحد (زي users) */
    protected function orderColumn(): ?string
    {
        return 'sort';
    }

    /** قواعد تحقق تغلب المتولّدة من الـ schema — $id بيبقى null وقت الإنشاء */
    protected function rules(?int $id): array
    {
        return [];
    }

    /** تعديل القيم قبل الحفظ — لتنضيف حقول مش أعمدة في الجدول مثلًا */
    protected function transform(array $data, ?Model $model): array
    {
        return $data;
    }

    /** بعد الحفظ — للعلاقات (أدوار، تاجات…) */
    protected function afterSave(Model $model, array $data): void
    {
        //
    }

    /** رسالة تمنع الحذف، أو null لو مسموح */
    protected function guardDelete(Model $model): ?string
    {
        return null;
    }

    /**
     * صلاحية زيادة مطلوبة للحذف فوق صلاحية فتح الشاشة.
     * الشاشة بتفتح لمدخل البيانات عشان يدخل ويعدّل، بس المسح قرار نهائي
     * — فبيفضل على اللي معاه النشر.
     */
    protected function deletePermission(): ?string
    {
        return null;
    }

    /**
     * فلتر الملكية. بيتطبّق على القائمة **وعلى أي وصول بالـ id** —
     * من غير التاني ده الوسيط بيكتب /admin/properties/5/edit في العنوان
     * ويعدّل وحدة مش بتاعته حتى لو القائمة مبتوريهاش.
     */
    protected function scope(Builder $query): Builder
    {
        return $query;
    }

    /** الصف مع تطبيق الـ scope — 404 لو مش من حقه */
    protected function findModel(int $id): Model
    {
        return $this->scope($this->modelClass()::query())->findOrFail($id);
    }

    public function index(Request $request): Response
    {
        $query = $this->scope($this->modelClass()::query())->with($this->with());

        if ($q = trim((string) $request->get('q'))) {
            $query->where(function ($sub) use ($q) {
                foreach ($this->searchable() as $col) {
                    $sub->orWhere($col, 'like', "%{$q}%");
                }
            });
        }

        $active = [];

        foreach ($this->listFilters() as $filter) {
            $value = (string) $request->get($filter['name'], '');
            $allowed = array_column($filter['options'], 'value');

            if ($value !== '' && in_array($value, $allowed, true)) {
                $query->where($filter['name'], $value);
                $active[$filter['name']] = $value;
            }
        }

        if ($order = $this->orderColumn()) {
            $query->orderBy($order);
        }

        $rows = $query->orderByDesc('id')->paginate(15)->withQueryString();

        // نحوّل كل صف لمصفوفة مسطّحة عشان الجدول يعرض قيم العلاقات كمان
        $rows->getCollection()->transform(fn (Model $row) => $this->rowPayload($row));

        return Inertia::render('Admin/Resource/Index', [
            'resource' => $this->schema(),
            'rows' => $rows,
            'activeFilters' => $active,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Resource/Form', [
            'resource' => $this->schema(),
            'item' => null,
        ]);
    }

    public function edit(int $id): Response
    {
        $item = $this->findModel($id);

        return Inertia::render('Admin/Resource/Form', [
            'resource' => $this->schema(),
            'item' => $this->itemPayload($item),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        $model = $this->modelClass()::create($this->transform($data, null));
        $this->afterSave($model, $data);

        return redirect()
            ->route("admin.{$this->key()}.index")
            ->with('success', 'تم الحفظ ✅');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $model = $this->findModel($id);
        $data = $this->validated($request, $id);

        $model->update($this->transform($data, $model));
        $this->afterSave($model, $data);

        return redirect()
            ->route("admin.{$this->key()}.index")
            ->with('success', 'تم التحديث ✅');
    }

    public function destroy(int $id): RedirectResponse
    {
        $model = $this->findModel($id);

        if (($permission = $this->deletePermission()) && ! auth()->user()?->can($permission)) {
            return back()->with('error', 'الحذف محتاج صلاحية الاعتماد والنشر.');
        }

        if ($reason = $this->guardDelete($model)) {
            return back()->with('error', $reason);
        }

        $model->delete();

        return redirect()
            ->route("admin.{$this->key()}.index")
            ->with('success', 'تم الحذف');
    }

    /** ------------------------------------------------------------------ */

    protected function schema(): array
    {
        return [
            'key' => $this->key(),
            'labels' => $this->labels(),
            'columns' => $this->columns(),
            'fields' => $this->fields(),
            'filters' => $this->listFilters(),
        ];
    }

    protected function rowPayload(Model $row): array
    {
        $data = $row->only(array_keys($this->columns()));
        $data['id'] = $row->id;

        // لو العمود بيشاور على علاقة (زي developer.name) بنجيبه يدوي
        foreach (array_keys($this->columns()) as $col) {
            if (str_contains($col, '.')) {
                [$rel, $attr] = explode('.', $col, 2);
                $data[$col] = $row->{$rel}?->{$attr};
            }
        }

        return $data;
    }

    /** قيم الفورم عند التعديل */
    protected function itemPayload(Model $model): array
    {
        $data = ['id' => $model->id];

        foreach ($this->fields() as $field) {
            $data[$field['name']] = $model->{$field['name']};
        }

        return $data;
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        $rules = [];

        foreach ($this->fields() as $f) {
            $rule = $f['required'] ?? false ? ['required'] : ['nullable'];

            $rule[] = match ($f['type'] ?? 'text') {
                'number' => 'integer',
                'toggle' => 'boolean',
                'date' => 'date',
                default => 'string',
            };

            if (! in_array($f['type'] ?? 'text', ['toggle', 'number', 'date'], true)) {
                $rule[] = 'max:2000';
            }

            $rules[$f['name']] = $rule;
        }

        // قواعد الكنترولر تغلب المتولّدة (unique، confirmed، إلخ)
        $rules = array_replace($rules, $this->rules($id));

        // أسماء الحقول في رسائل الخطأ بتيجي من labels بتاعة الـ schema
        $attributes = [];

        foreach ($this->fields() as $f) {
            $attributes[$f['name']] = $f['label'];
        }

        $data = $request->validate($rules, [], $attributes);

        // التوجل بيوصل string من الفورم أحيانًا
        foreach ($this->fields() as $f) {
            if (($f['type'] ?? '') === 'toggle') {
                $data[$f['name']] = (bool) ($data[$f['name']] ?? false);
            }
        }

        return $data;
    }
}
