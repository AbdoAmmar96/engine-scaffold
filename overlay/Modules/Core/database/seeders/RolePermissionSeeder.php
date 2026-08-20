<?php

namespace Modules\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * الصلاحيات والأدوار. قبل كده كان كل راوت أدمن على role:admin،
 * يعني editor و agent بيسجّلوا دخول وياخدوا 403 في كل حتة — أدوار على الورق بس.
 *
 * قاعدة العزل (مطبّقة في ResourceController::scope):
 *   معاه «manage catalog» ⇒ بيشوف كل الصفوف.
 *   مش معاه ⇒ بيشوف الصفوف اللي owner_id بتاعها = هو.
 * يعني الفرق بين المدير والوسيط صلاحية واحدة، مش شرط دور مكتوب في الكود.
 */
class RolePermissionSeeder extends Seeder
{
    /** الصلاحية => وصفها في الشاشة */
    public const PERMISSIONS = [
        'manage catalog' => 'كل العقارات والكمبوندات والمطوّرين والمناطق (بدون عزل)',
        'manage listings' => 'إضافة وتعديل وحداته هو بس',
        'manage projects' => 'إضافة وتعديل مشاريعه هو بس',
        'manage content' => 'المدونة ومكتبة الميديا والقوائم',
        'manage leads' => 'صندوق الطلبات',
        'manage settings' => 'إعدادات الموقع والهوية',
        'manage users' => 'المستخدمون والأدوار',
    ];

    /** الدور => صلاحياته · label = اسمه في اللوحة */
    public const ROLES = [
        'admin' => [
            'label' => 'مدير المنصّة',
            'note' => 'صلاحيات كاملة على كل حاجة',
            'permissions' => ['manage catalog', 'manage listings', 'manage projects', 'manage content', 'manage leads', 'manage settings', 'manage users'],
        ],
        'company' => [
            'label' => 'شركة / مطوّر عقاري',
            'note' => 'مشاريعها ووحداتها والطلبات الجاية عليها',
            'permissions' => ['manage listings', 'manage projects', 'manage leads'],
        ],
        'broker' => [
            'label' => 'وسيط عقاري',
            'note' => 'وحداته هو والطلبات الجاية عليها',
            'permissions' => ['manage listings', 'manage leads'],
        ],
        'editor' => [
            'label' => 'محرّر محتوى',
            'note' => 'المدونة والميديا والقوائم بس',
            'permissions' => ['manage content'],
        ],
        'customer' => [
            'label' => 'عميل',
            'note' => 'مفيش دخول للوحة — مساحته على الموقع نفسه',
            'permissions' => [],
        ],
    ];

    /** الأدوار اللي بتفتح لوحة التحكم (اللي ليها صلاحية واحدة على الأقل) */
    public static function staffRoles(): array
    {
        return array_keys(array_filter(self::ROLES, fn ($r) => $r['permissions'] !== []));
    }

    public function run(): void
    {
        foreach (array_keys(self::PERMISSIONS) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLES as $role => $config) {
            Role::findOrCreate($role, 'web')->syncPermissions($config['permissions']);
        }

        // «agent» كان اسم الوسيط قبل كده — بنحوّل أي حساب عليه لـ broker وبنشيله
        if ($old = Role::where('name', 'agent')->first()) {
            $broker = Role::findByName('broker', 'web');

            foreach ($old->users as $user) {
                /** @var User $user */
                $user->syncRoles([$broker]);
            }

            $old->delete();
        }

        Artisan::call('permission:cache-reset');

        $this->command?->info(sprintf(
            '  صلاحيات: %d · أدوار: %d',
            Permission::count(),
            Role::count(),
        ));
    }
}
