<?php

namespace Modules\Core\Http\Controllers;

use App\Models\User;
use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Modules\Core\Database\Seeders\RolePermissionSeeder;

/**
 * إدارة مستخدمي لوحة التحكم: إضافة/حذف حسابات، تغيير كلمات المرور، وتحديد الدور.
 * بيستخدم نفس شاشتي الريسورس العامّتين — الجديد هنا هو نوع الحقل password
 * وقواعد الحماية (مفيش حذف لنفسك، ولازم يفضل مدير واحد على الأقل).
 */
class UserAdminController extends ResourceController
{
    /** الأدوار جاية من RolePermissionSeeder — مصدر واحد للاسم والصلاحيات */
    private static function roles(): array
    {
        return RolePermissionSeeder::ROLES;
    }

    private static function roleLabel(?string $role): string
    {
        return self::roles()[$role]['label'] ?? '—';
    }

    protected function modelClass(): string { return User::class; }
    protected function key(): string { return 'users'; }

    protected function labels(): array
    {
        return ['plural' => 'المستخدمون', 'singular' => 'مستخدم'];
    }

    protected function searchable(): array { return ['name', 'email', 'phone', 'company_name']; }
    protected function with(): array { return ['roles']; }

    /** جدول users مفيهوش عمود sort */
    protected function orderColumn(): ?string { return null; }

    protected function columns(): array
    {
        return [
            'name' => 'الاسم',
            'email' => 'الإيميل',
            'phone' => 'الموبايل',
            'role_label' => 'الدور',
            'is_active' => 'مفعّل',
            'joined' => 'مضاف في',
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
            ['name' => 'email', 'label' => 'الإيميل', 'type' => 'text', 'required' => true,
                'hint' => 'ده اللي بيسجّل بيه الدخول — لازم يكون مش مكرّر'],
            ['name' => 'phone', 'label' => 'الموبايل', 'type' => 'text'],
            ['name' => 'company_name', 'label' => 'اسم الشركة', 'type' => 'text',
                'hint' => 'لحسابات الشركات — بيظهر بدل الاسم الشخصي'],
            ['name' => 'role', 'label' => 'الدور', 'type' => 'select', 'required' => true,
                'hint' => self::rolesHint(),
                'options' => array_map(
                    fn (array $config, string $value) => ['value' => $value, 'label' => $config['label'].' — '.$config['note']],
                    array_values(self::roles()),
                    array_keys(self::roles()),
                )],
            ['name' => 'password', 'label' => 'كلمة المرور', 'type' => 'password',
                'hint' => '8 حروف على الأقل — سيبها فاضية عند التعديل لو مش عايز تغيّرها'],
            ['name' => 'password_confirmation', 'label' => 'تأكيد كلمة المرور', 'type' => 'password'],
            ['name' => 'is_active', 'label' => 'مفعّل', 'type' => 'toggle',
                'hint' => 'الحساب الموقوف مايقدرش يسجّل دخول'],
        ];
    }

    /** ملخّص صلاحيات كل دور — عشان اللي بيعمل الحساب يعرف بيدّي إيه */
    private static function rolesHint(): string
    {
        $lines = [];

        foreach (self::roles() as $config) {
            $perms = array_map(
                fn (string $p) => RolePermissionSeeder::PERMISSIONS[$p] ?? $p,
                $config['permissions'],
            );

            $lines[] = $config['label'].': '.($perms ? implode(' · ', $perms) : 'مفيش صلاحيات لوحة');
        }

        return implode(' | ', $lines);
    }

    protected function rules(?int $id): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'role' => [
                'required',
                Rule::in(array_keys(self::roles())),
                function (string $attribute, mixed $value, callable $fail) use ($id) {
                    if ($id && $value !== 'admin' && $this->isLastAdmin($id)) {
                        $fail('ده آخر مدير — لازم يفضل في مدير واحد على الأقل.');
                    }
                },
            ],
            'is_active' => [
                'boolean',
                // توقيف آخر مدير = قفل اللوحة على الكل، فنفس حماية تغيير الدور
                function (string $attribute, mixed $value, callable $fail) use ($id) {
                    if ($id && ! $value && $this->isLastAdmin($id)) {
                        $fail('ده آخر مدير — مينفعش توقف حسابه.');
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
            'phone' => $data['phone'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
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
        /** @var User $row */
        $role = $row->roles->pluck('name')->first();

        return [
            'id' => $row->id,
            'name' => $row->displayName(),
            'email' => $row->email,
            'phone' => $row->phone ?: '—',
            'role_label' => self::roleLabel($role),
            'is_active' => (bool) $row->is_active,
            'joined' => $row->created_at?->format('Y/m/d'),
            '_self' => $row->id === auth()->id(),
            '_locked' => $this->guardDelete($row) !== null,
        ];
    }

    protected function itemPayload(Model $model): array
    {
        /** @var User $model */
        return [
            'id' => $model->id,
            'name' => $model->name,
            'email' => $model->email,
            'phone' => $model->phone ?? '',
            'company_name' => $model->company_name ?? '',
            'is_active' => (bool) $model->is_active,
            'role' => $model->roles->pluck('name')->first() ?? 'customer',
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
