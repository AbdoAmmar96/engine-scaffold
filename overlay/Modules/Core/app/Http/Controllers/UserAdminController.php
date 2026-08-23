<?php

namespace Modules\Core\Http\Controllers;

use App\Models\User;
use App\Support\ResourceController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Modules\Core\Database\Seeders\RolePermissionSeeder;

/**
 * إدارة الحسابات: إضافة/حذف، كلمات المرور، والدور.
 *
 * خط الفصل «manage roles»:
 *   معاه   ⇒ بيشوف ويعدّل كل الحسابات وبيوزّع أي دور (سوبر أدمن).
 *   من غيره ⇒ بيشوف حسابات الموقع بس (عميل/معلن) وبيوزّع أدوارها بس.
 * يعني المدير بيدير عملاء المنصّة، وفريق العمل نفسه مايتلمسش إلا من فوق.
 */
class UserAdminController extends ResourceController
{
    /** صلاحيات لو ضاع آخر حساب معاه واحدة منها، اللوحة بتقفل على نفسها */
    private const CRITICAL = [
        'manage roles' => 'ده آخر حساب بيقدر يوزّع الأدوار — لازم يفضل واحد على الأقل.',
        'manage users' => 'ده آخر حساب بيقدر يدير المستخدمين — لازم يفضل واحد على الأقل.',
    ];

    /**
     * الأدوار اللي المستخدم الحالي مسموح له يوزّعها.
     * مصدرها RolePermissionSeeder — مصدر واحد للاسم والصلاحيات.
     */
    private static function roles(): array
    {
        if (self::canManageRoles()) {
            return RolePermissionSeeder::ROLES;
        }

        $staff = RolePermissionSeeder::staffRoles();

        return array_diff_key(RolePermissionSeeder::ROLES, array_flip($staff));
    }

    private static function canManageRoles(): bool
    {
        return (bool) auth()->user()?->can('manage roles');
    }

    /** مفيش «manage roles» = حسابات الموقع بس، في القايمة وبالـ id */
    protected function scope(Builder $query): Builder
    {
        if (self::canManageRoles()) {
            return $query;
        }

        return $query->whereDoesntHave(
            'roles',
            fn ($q) => $q->whereIn('name', RolePermissionSeeder::staffRoles()),
        );
    }

    private static function roleLabel(?string $role): string
    {
        return RolePermissionSeeder::ROLES[$role]['label'] ?? '—';
    }

    protected function modelClass(): string
    {
        return User::class;
    }

    protected function key(): string
    {
        return 'users';
    }

    protected function labels(): array
    {
        return ['plural' => 'المستخدمون', 'singular' => 'مستخدم'];
    }

    protected function searchable(): array
    {
        return ['name', 'email', 'phone', 'company_name'];
    }

    protected function with(): array
    {
        return ['roles'];
    }

    /** جدول users مفيهوش عمود sort */
    protected function orderColumn(): ?string
    {
        return null;
    }

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
                    if (! $id) {
                        return;
                    }

                    $keeps = RolePermissionSeeder::ROLES[$value]['permissions'] ?? [];

                    foreach (self::CRITICAL as $permission => $message) {
                        if (! in_array($permission, $keeps, true) && $this->isLastWith($id, $permission)) {
                            $fail($message);
                        }
                    }
                },
            ],
            'is_active' => [
                'boolean',
                // توقيف آخر حساب معاه صلاحية حرجة = قفل اللوحة على نفسها
                function (string $attribute, mixed $value, callable $fail) use ($id) {
                    if (! $id || $value) {
                        return;
                    }

                    foreach (self::CRITICAL as $permission => $message) {
                        if ($this->isLastWith($id, $permission)) {
                            $fail($message);
                        }
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

        foreach (self::CRITICAL as $permission => $message) {
            if ($this->isLastWith($model->id, $permission)) {
                return $message;
            }
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

    /**
     * آخر حساب معاه الصلاحية دي؟
     *
     * قبل كده الحماية كانت على دور «admin» بالاسم. بعد ما بقى فيه سوبر
     * أدمن، الدور ده ممكن يفضى والقفل يتفتح من غير ما حد ياخد باله —
     * فالحماية بقت على الصلاحية نفسها.
     */
    private function isLastWith(int $id, string $permission): bool
    {
        $user = User::find($id);

        if (! $user?->can($permission)) {
            return false;
        }

        return User::permission($permission)
            ->where('id', '!=', $id)
            ->where('is_active', true)
            ->doesntExist();
    }
}
