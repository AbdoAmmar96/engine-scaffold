<?php

namespace Modules\Properties\Notifications;

use App\Support\PasswordLink;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Core\Services\SettingsService;
use Modules\Properties\Models\Property;

/**
 * «وصلتنا وحدتك» — بتتبعت للي عرض وحدة من الفورم العام وهو مش مسجّل دخول.
 *
 * ليه رسالة لوحدها مش رسالة استعادة كلمة السر العادية: اللي بيوصله دي
 * **ماطلبش** يغيّر كلمة سر — طلب يعرض وحدة. رسالة «فيه طلب لتغيير كلمة
 * المرور» في السياق ده بتبان تصيّد وبتترمي.
 *
 * وبتفرّق بين حالتين، لأن الوعد مختلف:
 * - حساب جديد: محتاج يحدّد كلمة سر عشان يوصل لوحدته.
 * - حساب موجود: مش محتاج حاجة — بس لازم يعرف إن فيه وحدة اتحطّت باسمه،
 *   عشان لو مش هو اللي عملها يبلّغ.
 */
class ListingReceivedNotification extends Notification
{
    public function __construct(
        private readonly Property $property,
        // مش $locale: الاسم ده محجوز في Notification الأساسية
        private readonly string $lang = 'ar',
        private readonly bool $isNewAccount = true,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $site = app(SettingsService::class)->get('general', 'site_name', config('app.name'));
        $en = $this->lang === 'en';
        $title = $this->property->t('title', $this->lang) ?: $this->property->title;

        return $this->isNewAccount
            ? $this->newAccount($notifiable, $site, $en, $title)
            : $this->existingAccount($site, $en, $title);
    }

    private function newAccount(object $notifiable, string $site, bool $en, string $title): MailMessage
    {
        // التوكن بيتعمل هنا مش وقت الإنشاء: لو الرسالة ماتبعتتش، مفيش توكن معلّق
        $link = $notifiable instanceof CanResetPassword
            ? PasswordLink::fresh($notifiable)
            : url('/'.$this->lang.'/login');

        $minutes = PasswordLink::expiresIn();

        return $en
            ? (new MailMessage)
                ->subject("We received your property — {$site}")
                ->greeting('Hello,')
                ->line("We received your listing: {$title}.")
                ->line('Our team reviews it and publishes it within 24 hours.')
                ->line("We opened an account for you so you can follow it and get buyer requests directly. Set your password to activate it:")
                ->action('Set your password', $link)
                ->line("This link expires in {$minutes} minutes — you can request a new one from \"Forgot password\".")
                ->line("If you did not submit this property, ignore this email and contact us.")
                ->salutation("Regards, {$site}")
            : (new MailMessage)
                ->subject("وصلتنا وحدتك — {$site}")
                ->greeting('أهلًا،')
                ->line("وصلنا عرض وحدتك: {$title}.")
                ->line('الفريق بيراجعها وبينشرها خلال ٢٤ ساعة.')
                ->line('فتحنالك حساب عشان تتابعها وتوصلك طلبات المشترين على طول. حدّد كلمة السر عشان تفعّله:')
                ->action('حدّد كلمة السر', $link)
                ->line("اللينك بينتهي بعد {$minutes} دقيقة — تقدر تطلب واحد جديد من «نسيت كلمة المرور».")
                ->line('لو مش إنت اللي عرضت الوحدة دي، تجاهل الرسالة وكلّمنا.')
                ->salutation("تحياتنا، {$site}");
    }

    private function existingAccount(string $site, bool $en, string $title): MailMessage
    {
        $account = url('/'.$this->lang.'/account/my-properties');

        return $en
            ? (new MailMessage)
                ->subject("A property was added to your account — {$site}")
                ->greeting('Hello,')
                ->line("A new listing was submitted under your account: {$title}.")
                ->line('Our team reviews it and publishes it within 24 hours.')
                ->action('Open my properties', $account)
                ->line('If this was not you, sign in and delete it — or contact us.')
                ->salutation("Regards, {$site}")
            : (new MailMessage)
                ->subject("وحدة جديدة على حسابك — {$site}")
                ->greeting('أهلًا،')
                ->line("اتعرضت وحدة جديدة على حسابك: {$title}.")
                ->line('الفريق بيراجعها وبينشرها خلال ٢٤ ساعة.')
                ->action('افتح «وحداتي»', $account)
                ->line('لو مش إنت اللي عملت ده، ادخل حسابك واحذفها — أو كلّمنا.')
                ->salutation("تحياتنا، {$site}");
    }
}
