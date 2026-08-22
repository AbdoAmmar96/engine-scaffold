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
 * قاعدتين بتحكموا كل حاجة (متطبّقين في ResourceController و PropertyAdminController):
 *   «manage catalog»   ⇒ بيشوف كل الصفوف · من غيرها بيشوف بتاعه بس.
 *   «publish listings» ⇒ بيعتمد وينشر ويحذف · من غيرها بيعدّل وبس.
 * يعني الفرق بين المدير ومدخل البيانات صلاحية واحدة، مش شرط دور في الكود.
 *
 * و staff بتحدد مين بيفتح اللوحة أصلًا — المعلن معاه «manage listings»
 * لكنه مش موظف، فمساحته على الموقع مش /admin.
 */
class RolePermissionSeeder extends Seeder
{
    /** الصلاحية => وصفها في الشاشة */
    public const PERMISSIONS = [
        'manage catalog' => 'كل العقارات والكمبوندات والمطوّرين والمناطق (بدون عزل)',
        'publish listings' => 'اعتماد العقارات ونشرها وحذفها',
        'feature listings' => 'تمييز الإعلانات في نتايج البحث',
        'manage listings' => 'إضافة وتعديل وحداته هو بس',
        'manage projects' => 'إضافة وتعديل مشاريعه هو بس',
        'manage content' => 'المدونة والقوائم وصفحات الهبوط',
        'manage media' => 'مكتبة الميديا',
        'manage leads' => 'صندوق الطلبات',
        'manage settings' => 'إعدادات الموقع والهوية',
        'manage users' => 'المستخدمون',
        'manage roles' => 'أدوار فريق العمل',
    ];

    /**
     * الدور => label اسمه في اللوحة · note وصفه · staff بيفتح اللوحة؟
     *
     * الترتيب هنا هو ترتيب القايمة في شاشة المستخدمين، فمن الأعلى صلاحية للأقل.
     */
    public const ROLES = [
        'super_admin' => [
            'label' => 'سوبر أدمن',
            'note' => 'كل حاجة + الأدوار والصلاحيات',
            'staff' => true,
            'permissions' => [
                'manage catalog', 'publish listings', 'feature listings',
                'manage listings', 'manage projects', 'manage content', 'manage media',
                'manage leads', 'manage settings', 'manage users', 'manage roles',
            ],
        ],
        'admin' => [
            'label' => 'مدير المنصّة',
            'note' => 'كل حاجة ماعدا توزيع أدوار الفريق',
            'staff' => true,
            'permissions' => [
                'manage catalog', 'publish listings', 'feature listings',
                'manage listings', 'manage projects', 'manage content', 'manage media',
                'manage leads', 'manage settings', 'manage users',
            ],
        ],
        'data_entry' => [
            'label' => 'مدخل بيانات',
            'note' => 'يدخل ويعدّل العقارات والمشاريع — من غير نشر ولا حذف',
            'staff' => true,
            'permissions' => ['manage catalog', 'manage media'],
        ],
        'marketing' => [
            'label' => 'مسؤول تسويق',
            'note' => 'تمييز الإعلانات وصفحات الهبوط والمدونة',
            'staff' => true,
            'permissions' => ['manage catalog', 'feature listings', 'manage content', 'manage media'],
        ],
        'editor' => [
            'label' => 'محرّر محتوى',
            'note' => 'المدونة وصفحات الهبوط والقوائم والميديا',
            'staff' => true,
            'permissions' => ['manage content', 'manage media'],
        ],
        'consultant' => [
            'label' => 'مستشار عقاري',
            'note' => 'الطلبات المسنودة له هو بس',
            'staff' => true,
            'permissions' => ['manage leads'],
        ],
        'company' => [
            'label' => 'شركة / مطوّر عقاري',
            'note' => 'مشاريعها ووحداتها والطلبات الجاية عليها',
            'staff' => true,
            'permissions' => ['manage listings', 'manage projects', 'manage leads'],
        ],
        'broker' => [
            'label' => 'وسيط عقاري',
            'note' => 'وحداته هو والطلبات الجاية عليها',
            'staff' => true,
            'permissions' => ['manage listings', 'manage leads'],
        ],
        'lister' => [
            'label' => 'معلن / مالك عقار',
            'note' => 'وحداته من «حسابي» على الموقع — مفيش دخول للوحة',
            'staff' => false,
            'permissions' => ['manage listings'],
        ],
        'customer' => [
            'label' => 'عميل',
            'note' => 'مفيش دخول للوحة — مساحته على الموقع نفسه',
            'staff' => false,
            'permissions' => [],
        ],
    ];

    /**
     * الأدوار اللي بتفتح لوحة التحكم.
     *
     * الفلاج صريح مش مستنتج من «عنده صلاحية»: المعلن معاه «manage listings»
     * عشان يدير وحداته من حسابه، ولو استنتجنا كان هيتحسب موظف وياخد اللوحة.
     *
     * @return string[]
     */
    public static function staffRoles(): array
    {
        return array_keys(array_filter(self::ROLES, fn (array $r) => $r['staff']));
    }

    /** الأدوار اللي ينفع تملك وحدة أو مشروع */
    public static function ownerRoles(): array
    {
        return array_keys(array_filter(
            self::ROLES,
            fn (array $r) => in_array('manage listings', $r['permissions'], true) && ! in_array('manage catalog', $r['permissions'], true),
        ));
    }

    public function run(): void
    {
        foreach (array_keys(self::PERMISSIONS) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLES as $role => $config) {
            Role::findOrCreate($role, 'web')->syncPermissions($config['permissions']);
        }

        $this->retireOldRoles();
        $this->ensureSuperAdmin();

        Artisan::call('permission:cache-reset');

        $this->command?->info(sprintf(
            '  صلاحيات: %d · أدوار: %d',
            Permission::count(),
            Role::count(),
        ));
    }

    /** «agent» كان اسم الوسيط قبل كده — بنحوّل أي حساب عليه لـ broker وبنشيله */
    private function retireOldRoles(): void
    {
        if (! $old = Role::where('name', 'agent')->first()) {
            return;
        }

        $broker = Role::findByName('broker', 'web');

        foreach ($old->users as $user) {
            /** @var User $user */
            $user->syncRoles([$broker]);
        }

        $old->delete();
    }

    /**
     * أول تشغيل بعد ما الدور اتضاف: لازم يفضل في حد يقدر يوزّع الأدوار،
     * فأقدم مدير بيترقّى. من غير ده اللوحة بتقفل على نفسها —
     * دور «manage roles» موجود ومحدش معاه.
     */
    private function ensureSuperAdmin(): void
    {
        if (User::role('super_admin')->exists()) {
            return;
        }

        $first = User::role('admin')->orderBy('id')->first();

        $first?->syncRoles(['super_admin']);

        $first && $this->command->info("  {$first->email} اترقّى لسوبر أدمن (أول واحد بس)");
    }
}
