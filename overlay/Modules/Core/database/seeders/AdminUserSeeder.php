<?php

namespace Modules\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // الأدوار مصدرها RolePermissionSeeder (بيتشغّل قبل ده).
        // كان فيه هنا findOrCreate لـ 'agent' فكان بيرجّعه بعد ما السيدر التاني
        // يحوّله لـ broker ويمسحه — يعني الدور القديم عمره ما كان بيموت.

        $email = env('ADMIN_EMAIL', 'admin@bp-eg.com');
        $exists = User::where('email', $email)->exists();

        // كلمة المرور بتتحط مرة واحدة عند الإنشاء بس.
        // قبل كده updateOrCreate كان بيرجّع الباسورد لأصله مع كل reseed،
        // يعني أي كلمة مرور غيّرتها من اللوحة كانت بتتلغي.
        $password = env('ADMIN_PASSWORD') ?: Str::password(16, symbols: false);

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'مدير المنصة'),
                'password' => $password,
            ],
        );

        // الحساب الموجود مبيتلمسش دوره.
        //
        // كان هنا syncRoles(['admin']) على طول، والسيدر ده بيتشغّل بعد
        // RolePermissionSeeder — فكل ديبلوي كان بيلغي ترقية السوبر أدمن
        // اللي السيدر التاني عملها، وكمان بيرجّع أي دور اتغيّر من اللوحة.
        // النتيجة: صلاحية «manage roles» موجودة ومحدش معاه.
        if ($exists) {
            if ($admin->roles()->doesntExist()) {
                $admin->syncRoles(['super_admin']);
            }

            $this->command?->info("  المدير موجود ({$email}) — الدور وكلمة المرور مالهمش دعوة.");

            return;
        }

        // أول حساب على تثبيت جديد لازم يقدر يعمل باقي الفريق كمان
        $admin->syncRoles(['super_admin']);

        $this->command?->warn("  اتعمل سوبر أدمن: {$email}");
        $this->command?->warn("  كلمة المرور: {$password}");
        $this->command?->warn('  احفظها دلوقتي — مش هتتعرض تاني.');
    }
}
