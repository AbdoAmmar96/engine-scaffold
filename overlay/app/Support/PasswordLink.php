<?php

namespace App\Support;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;

/**
 * لينك تحديد/تغيير كلمة السر.
 *
 * موجود في مكان واحد لأن مصدرين بيبنوه: رسالة «نسيت كلمة المرور»
 * ورسالة «عملنالك حساب». لو اتكتب مرتين، أول ما مسار الراوت يتغيّر
 * واحدة منهم بتفضل تبعت لينك ميت — ولينك ميت في إيميل محدش بيكتشفه
 * غير لما عميل يشتكي.
 */
class PasswordLink
{
    public static function url(CanResetPassword $user, string $token): string
    {
        return url(sprintf(
            '/%s/reset-password/%s?email=%s',
            app()->getLocale(),
            $token,
            urlencode($user->getEmailForPasswordReset()),
        ));
    }

    /** توكن جديد + لينكه — للرسايل اللي بنبعتها بنفسنا مش عن طريق البروكر */
    public static function fresh(CanResetPassword $user): string
    {
        return self::url($user, Password::broker()->createToken($user));
    }

    /** بالدقايق — نفس اللي مكتوب في الرسالة */
    public static function expiresIn(): int
    {
        return (int) config('auth.passwords.users.expire', 60);
    }
}
