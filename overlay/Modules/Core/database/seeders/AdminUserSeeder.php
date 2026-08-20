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

        $admin->syncRoles(['admin']);

        if ($exists) {
            $this->command?->info("  المدير موجود ({$email}) — كلمة المرور مالهاش دعوة.");

            return;
        }

        $this->command?->warn("  اتعمل مدير: {$email}");
        $this->command?->warn("  كلمة المرور: {$password}");
        $this->command?->warn('  احفظها دلوقتي — مش هتتعرض تاني.');
    }
}
