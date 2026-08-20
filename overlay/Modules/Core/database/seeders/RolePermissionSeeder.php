<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * الصلاحيات والأدوار. قبل كده كان كل راوت أدمن على role:admin،
 * يعني editor و agent بيسجّلوا دخول وياخدوا 403 في كل حتة — أدوار على الورق بس.
 */
class RolePermissionSeeder extends Seeder
{
    /** الصلاحية => وصفها في الشاشة */
    public const PERMISSIONS = [
        'manage catalog' => 'العقارات والكمبوندات والمطوّرين والمناطق',
        'manage content' => 'المدونة ومكتبة الميديا والقوائم',
        'manage leads' => 'صندوق الطلبات',
        'manage settings' => 'إعدادات الموقع والهوية',
        'manage users' => 'المستخدمون والأدوار',
    ];

    /** الدور => صلاحياته */
    public const ROLES = [
        'admin' => ['manage catalog', 'manage content', 'manage leads', 'manage settings', 'manage users'],
        'editor' => ['manage catalog', 'manage content'],
        'agent' => ['manage leads'],
    ];

    public function run(): void
    {
        foreach (array_keys(self::PERMISSIONS) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLES as $role => $permissions) {
            Role::findOrCreate($role, 'web')->syncPermissions($permissions);
        }

        Artisan::call('permission:cache-reset');

        $this->command?->info(sprintf(
            '  صلاحيات: %d · أدوار: %d',
            Permission::count(),
            Role::count(),
        ));
    }
}
