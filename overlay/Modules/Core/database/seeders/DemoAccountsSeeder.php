<?php

namespace Modules\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * حساب تجريبي لكل دور عشان تجرّب الصلاحيات من غير ما تعمل حسابات بإيدك.
 *
 * firstOrCreate مش updateOrCreate: لو الحساب موجود مبنلمسش كلمة مروره —
 * السيدرز بتتشغّل في كل رفعة، وإعادة تعيين كلمة مرور حساب شغّال ثغرة.
 */
class DemoAccountsSeeder extends Seeder
{
    /** الدور => [الاسم، الإيميل، اسم الشركة] */
    private const ACCOUNTS = [
        'company' => ['شركة المروج للتطوير', 'company@bp-eg.com', 'شركة المروج للتطوير'],
        'broker' => ['كريم الوسيط', 'broker@bp-eg.com', null],
        'data_entry' => ['مدخل البيانات', 'data@bp-eg.com', null],
        'marketing' => ['مسؤول التسويق', 'marketing@bp-eg.com', null],
        'consultant' => ['المستشار العقاري', 'consultant@bp-eg.com', null],
        'editor' => ['محرّر المحتوى', 'editor@bp-eg.com', null],
        'lister' => ['مالك عقار', 'lister@bp-eg.com', null],
        'customer' => ['عميل تجريبي', 'customer@bp-eg.com', null],
    ];

    public function run(): void
    {
        $created = [];

        foreach (self::ACCOUNTS as $role => [$name, $email, $company]) {
            $existing = User::where('email', $email)->first();

            if ($existing) {
                // بنتأكد بس إن الدور متظبّط، من غير ما نلمس الباسورد
                $existing->syncRoles([$role]);

                continue;
            }

            $password = Str::password(14, symbols: false);

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'company_name' => $company,
                'password' => $password,
                'is_active' => true,
            ]);

            $user->syncRoles([$role]);

            $created[] = [$role, $email, $password];
        }

        if ($created === []) {
            $this->command?->info('  الحسابات التجريبية موجودة — مالمستش كلمات المرور');

            return;
        }

        $this->command?->warn('  ⚠ الحسابات التجريبية (اكتبها دلوقتي — مش هتتعرض تاني):');

        foreach ($created as [$role, $email, $password]) {
            $this->command?->warn(sprintf('    %-9s %-24s %s', $role, $email, $password));
        }
    }
}
