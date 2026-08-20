<?php

namespace Modules\Core\Http\Controllers;

use App\Models\User;
use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * إدارة مستخدمي لوحة التحكم: إضافة/حذف حسابات، تغيير كلمات المرور، وتحديد الدور.
 * بيستخدم نفس شاشتي الريسورس العامّتين — الجديد هنا هو نوع الحقل password
 * وقواعد الحماية (مفيش حذف لنفسك، ولازم يفضل مدير واحد على الأقل).
 */
class UserAdminController extends ResourceController
{
    /** الأدوار المتاحة وأسماؤها بالعربي */
    private const ROLES = [
        'admin' => 'مدير — صلاحيات كاملة',
        'editor' => 'محرّر — محتوى الموقع',
        'agent' => 'مسوّق عقاري',
    ];

    protected function modelClass(): string { return User::class; }
    protected function key(): string { return 'users'; }

    protected function labels(): array
    {
        return ['plural' => 'المستخدمون', 'singular' => 'مستخدم'];
    }

    protected function searchable(): array { return ['name', 'email']; }
    protected function with(): array { return ['roles']; }

    /** جدول users مفيهوش عمود sort */
    protected function orderColumn(): ?string { return null; }

    protected function columns(): array
    {
        return [
            'name' => 'الاسم',
            'email' => 'الإيميل',
            'role_label' => 'الدور',
            'joined' => 'مضاف في',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
            ['name' => 'email', 'label' => 'الإيميل', 'type' => 'text', 'required' => true,
                'hint' => 'ده اللي بيسجّل بيه الدخول — لازم يكون مش مكرّر'],
            ['name' => 'role', 'label' => 'الدور', 'type' => 'select', 'required' => true,
                'hint' => 'دلوقتي «مدير» بس هو اللي بيفتح اللوحة — الأدوار التانية متسجّلة استعدادًا للصلاحيات التفصيلية.',
                'options' => array_map(
                fn ($label, $value) => ['value' => $value, 'label' => $label],
                array_values(self::ROLES),
                array_keys(self::ROLES),
            )],
            ['name' => 'password', 'label' => 'كلمة المرور', 'type' => 'password',
                'hint' => '8 حروف على الأقل — سيبها فاضية عند التعديل لو مش عايز تغيّرها'],
            ['name' => 'password_confirmation', 'label' => 'تأكيد كلمة المرور', 'type' => 'password'],
        ];
    }

    protected function rules(?int $id): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($id)],
            'role' => [
                'required',
                Rule::in(array_keys(self::ROLES)),
                function (string $attribute, mixed $value, callable $fail) use ($id) {
                    if ($id && $value !== 'admin' && $this->isLastAdmin($id)) {
                        $fail('ده آخر مدير — لازم يفضل في مدير واحد على الأقل.');
                    }
                },
            ],
            // فاضية عند التعديل = سيبها زي ما هي
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['nullable', 'string'],
        ];
    }

    /** role و password_confirmation مش أعمدة في الجدول */
    protected function transform(array $data, ?Model $model): array
    {
        $out = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        // الكاست 'hashed' في الموديل بيشفّرها لوحده
        if (filled($data['password'] ?? null)) {
            $out['password'] = $data['password'];
        }

        return $out;
    }

    protected function afterSave(Model $model, array $data): void
    {
        $model->syncRoles([$data['role']]);
    }

    protected function guardDelete(Model $model): ?string
    {
        if ($model->id === auth()->id()) {
            return 'مش هينفع تحذف الحساب اللي انت مسجّل بيه دلوقتي.';
        }

        if ($this->isLastAdmin($model->id)) {
            return 'ده آخر مدير — لازم يفضل في مدير واحد على الأقل.';
        }

        return null;
    }

    protected function rowPayload(Model $row): array
    {
        $role = $row->roles->pluck('name')->first();

        return [
            'id' => $row->id,
            'name' => $row->name,
            'email' => $row->email,
            'role_label' => self::ROLES[$role] ?? '—',
            'joined' => $row->created_at?->format('Y/m/d'),
            '_self' => $row->id === auth()->id(),
            '_locked' => $this->guardDelete($row) !== null,
        ];
    }

    protected function itemPayload(Model $model): array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'email' => $model->email,
            'role' => $model->roles->pluck('name')->first() ?? 'editor',
            // مبنبعتش الهاش للمتصفح أبدًا
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    private function isLastAdmin(int $id): bool
    {
        $user = User::find($id);

        if (! $user?->hasRole('admin')) {
            return false;
        }

        return User::role('admin')->where('id', '!=', $id)->doesntExist();
    }
}
