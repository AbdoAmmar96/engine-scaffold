<?php

namespace Modules\Properties\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Modules\Core\Services\SettingsService;
use Modules\Properties\Models\Property;
use Modules\Properties\Models\SavedSearch;

/**
 * «نزل اللي بتدوّر عليه» — تنبيه البحث المحفوظ.
 *
 * الرسالة بتذكر الوحدات نفسها مش «فيه جديد، ادخل شوف»: العميل لازم
 * يقرر من الإيميل يستاهل يفتح ولا لأ.
 */
class NewMatchesNotification extends Notification
{
    /** @param  Collection<int, Property>  $matches */
    public function __construct(
        private readonly SavedSearch $search,
        private readonly Collection $matches,
        // مش $locale: الاسم ده محجوز في Notification الأساسية
        private readonly string $lang = 'ar',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $site = app(SettingsService::class)->get('general', 'site_name', config('app.name'));
        $count = $this->matches->count();

        $mail = (new MailMessage)
            ->subject("{$count} وحدة جديدة في «{$this->search->name}» — {$site}")
            ->greeting('أهلًا،')
            ->line("نزل {$count} عرض جديد مطابق للبحث اللي حافظه باسم «{$this->search->name}».");

        foreach ($this->matches as $property) {
            $mail->line(sprintf(
                '• %s — %s%s · %s',
                $property->t('title', $this->lang),
                $property->priceLabel($this->lang),
                $property->size ? ' · '.$property->size.' م²' : '',
                ($property->location ?? $property->compound?->location)?->t('name', $this->lang) ?? '',
            ));
        }

        return $mail
            ->action('شوف النتايج كلها', url($this->search->url($this->lang)))
            ->line('لو مش عايز التنبيه ده، تقدر توقفه من «البحث المحفوظ» في حسابك.')
            ->salutation("تحياتنا، {$site}");
    }
}
