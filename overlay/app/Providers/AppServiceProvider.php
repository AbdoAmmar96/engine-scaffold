<?php

namespace App\Providers;

use App\Support\PasswordLink;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Services\SettingsService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configurePasswordResetMail();
    }

    /**
     * رسالة استعادة كلمة المرور.
     *
     * الافتراضية إنجليزية وباسم "Laravel"، وبتبني اللينك على راوت
     * password.reset اللي مش موجود عندنا — الراوت بتاعنا تحت بادئة اللغة.
     * فالاتنين بيتحدّدوا هنا: اللينك والنص.
     */
    private function configurePasswordResetMail(): void
    {
        $link = fn (CanResetPassword $notifiable, string $token) => PasswordLink::url($notifiable, $token);

        ResetPassword::createUrlUsing($link);

        // toMailUsing بياخد التوكن مش اللينك — بيعدّي على resetUrl() من فوقها،
        // فالكولباك اللي فوق مبيتناديش هنا واللينك لازم يتبني تاني بنفس المصدر
        ResetPassword::toMailUsing(function (CanResetPassword $notifiable, string $token) use ($link) {
            $site = app(SettingsService::class)->get('general', 'site_name', config('app.name'));
            $minutes = PasswordLink::expiresIn();

            return (new MailMessage)
                ->subject("استعادة كلمة المرور — {$site}")
                ->greeting('أهلًا،')
                ->line("وصلنا طلب لتغيير كلمة المرور بتاعة حسابك على {$site}.")
                ->action('غيّر كلمة المرور', $link($notifiable, $token))
                ->line("اللينك ده بينتهي بعد {$minutes} دقيقة.")
                ->line('لو مش إنت اللي طلبت، تجاهل الرسالة — حسابك زي ما هو ومحدش هيقدر يغيّر حاجة.')
                ->salutation("تحياتنا، {$site}");
        });
    }
}
