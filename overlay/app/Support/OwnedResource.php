<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Database\Seeders\RolePermissionSeeder;

/**
 * صفوف ليها مالك: الوسيط والشركة يشوفوا بتاعهم بس، والأدمن يشوف الكل.
 * بيتركّب على أي ResourceController فيه عمود owner_id.
 *
 * خط الفصل هو صلاحية «manage catalog» مش اسم الدور — يعني تقدر تعمل
 * دور جديد وتديله الصلاحية فيشوف الكل، من غير ما تلمس كود.
 */
trait OwnedResource
{

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

    /**
     * حسابات الوسطاء والشركات.
     *
     * الإيميل بيان شخصي، فبيبان لمين بيدير المستخدمين بس — قبل كده كان
     * ظاهر لأي حد معاه «manage catalog»، يعني مدخل البيانات والتسويق كانوا
     * بيقروا إيميلات كل الوسطاء من قايمة منسدلة. رقم الحساب بيفرّق بين
     * اتنين بنفس الاسم من غير ما يكشف حاجة.
     */
    protected static function ownerOptions(): array
    {
        $seesEmails = (bool) self::actor()?->can('manage users');

        return User::role(RolePermissionSeeder::ownerRoles())
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'value' => (string) $u->id,
                'label' => $seesEmails
                    ? $u->displayName().' — '.$u->email
                    : $u->displayName().' #'.$u->id,
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

    /**
     * صاحب الصف بيمسح بتاعه من غير أي صلاحية زيادة — هو اللي حطّه.
     * اللي بيشوف الكل (مدخل بيانات، تسويق) لازم يكون معاه صلاحية النشر،
     * وإلا كان هيقدر يمسح شغل غيره وهو أصلًا مش مسموح له ينشر.
     */
    protected function deletePermission(): ?string
    {
        return self::actorSeesEverything() ? 'publish listings' : null;
    }

    /** قاعدة تحقق لحقل المالك */
    protected function ownerRules(): array
    {
        return [$this->ownerColumn() => ['nullable', 'integer', 'exists:users,id']];
    }
}
