<?php

namespace Modules\Properties\Console;

use Illuminate\Console\Command;
use Modules\Properties\Models\SavedSearch;
use Modules\Properties\Notifications\NewMatchesNotification;

/**
 * تنبيهات البحث المحفوظ.
 *
 * بيتشغّل من الجدولة يوميًا. العلامة بتتحدّث حتى لو مفيش نتايج جديدة؟
 * لأ — بتتحدّث بس لما يتبعت تنبيه، عشان لو الإرسال وقع الوحدات
 * ما تضيعش من العميل.
 */
class SendSearchAlerts extends Command
{
    protected $signature = 'searches:alert {--limit=6 : أقصى عدد وحدات في الرسالة الواحدة}';

    protected $description = 'إرسال تنبيهات البحث المحفوظ للعملاء';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $sent = 0;
        $skipped = 0;

        SavedSearch::query()
            ->where('alerts', true)
            ->with('user')
            ->chunkById(100, function ($searches) use ($limit, &$sent, &$skipped) {
                foreach ($searches as $search) {
                    /** @var SavedSearch $search */
                    $user = $search->user;

                    // الحساب الموقوف مبياخدش تنبيهات.
                    // الحساب نفسه مضمون: FK بـ cascade، فبيتمسح مع صاحبه
                    if (! $user->is_active || blank($user->email)) {
                        $skipped++;

                        continue;
                    }

                    $matches = $search->newMatches($limit);

                    if ($matches->isEmpty()) {
                        continue;
                    }

                    $user->notify(new NewMatchesNotification($search, $matches));

                    // العلامة بتتحدّث بعد الإرسال بس
                    $search->update([
                        'last_property_id' => (int) $matches->max('id'),
                        'last_alert_at' => now(),
                    ]);

                    $sent++;
                }
            });

        $this->info("  تنبيهات اتبعتت: {$sent}".($skipped ? " · حسابات اتخطّت: {$skipped}" : ''));

        return self::SUCCESS;
    }
}
