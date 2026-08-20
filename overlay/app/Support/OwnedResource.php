<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * صفوف ليها مالك: الوسيط والشركة يشوفوا بتاعهم بس، والأدمن يشوف الكل.
 * بيتركّب على أي ResourceController فيه عمود owner_id.
 *
 * خط الفصل هو صلاحية «manage catalog» مش اسم الدور — يعني تقدر تعمل
 * دور جديد وتديله الصلاحية فيشوف الكل، من غير ما تلمس كود.
 */
trait OwnedResource
{
    /** الأدوار اللي ينفع تكون مالكة لصف */
    protected const OWNER_ROLES = ['broker', 'company'];

    protected function ownerColumn(): string
    {
        return 'owner_id';
    }

    protected function scope(Builder $query): Builder
    {
        $user = self::actor();

        if ($user && $user->seesEverything()) {
            return $query;
        }

        // مفيش يوزر (مستحيل جوه auth) = مفيش صفوف، مش كل الصفوف
        return $query->where($this->ownerColumn(), $user?->id ?? 0);
    }

    /** المستخدم الحالي كـ User — auth()->user() بترجّع Authenticatable عام */
    protected static function actor(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    /** بيشوف كل الصفوف؟ */
    protected static function actorSeesEverything(): bool
    {
        return (bool) self::actor()?->seesEverything();
    }

    /**
     * الأدمن بس هو اللي بيختار المالك؛ الوسيط والشركة الحقل ده مش بيظهرلهم
     * أصلًا وبياخدوا ملكيتهم تلقائيًا في applyOwner().
     */
    protected function ownerField(string $label = 'الحساب المالك'): array
    {
        if (! self::actorSeesEverything()) {
            return [];
        }

        return [[
            'name' => $this->ownerColumn(),
            'label' => $label,
            'type' => 'select',
            'hint' => 'سيبه فاضي لو الصف تبع المنصّة نفسها',
            'options' => self::ownerOptions(),
        ]];
    }

    /** حسابات الوسطاء والشركات */
    protected static function ownerOptions(): array
    {
        return User::role(self::OWNER_ROLES)
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'value' => (string) $u->id,
                'label' => $u->displayName().' — '.$u->email,
            ])
            ->all();
    }

    /** غير الأدمن: الملكية بتتفرض من السيرفر، مهما بعت في الفورم */
    protected function applyOwner(array $data, ?Model $model): array
    {
        if (self::actorSeesEverything()) {
            return $data;
        }

        $column = $this->ownerColumn();
        $current = $model instanceof Model ? $model->getAttribute($column) : null;

        $data[$column] = $current ?? auth()->id();

        return $data;
    }

    /** قاعدة تحقق لحقل المالك */
    protected function ownerRules(): array
    {
        return [$this->ownerColumn() => ['nullable', 'integer', 'exists:users,id']];
    }
}
