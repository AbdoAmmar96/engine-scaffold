<?php

namespace Modules\Seo\Console;

use App\Support\Catalog;
use Illuminate\Console\Command;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;
use Modules\Seo\Models\LandingPage;

/**
 * توليد صفحات الهبوط البرمجية من الوحدات المنشورة.
 *
 * القاعدة: صفحة بتتعمل للتركيبة اللي عندها وحدات فعلًا بس. صفحة بتوعد
 * جوجل بـ«شقق للبيع في الإسكندرية» وتفتح فاضية أسوأ من إنها متتعملش.
 *
 * الأمر آمن لو اتشغّل أكتر من مرة: التركيبة الموجودة بتتحدّث مش بتتكرّر،
 * والنصوص اللي المحرّر كتبها مبتتلمسش خالص.
 */
class GenerateLandingPages extends Command
{
    protected $signature = 'seo:landing-pages
        {--min=1 : أقل عدد وحدات عشان التركيبة تستاهل صفحة}
        {--prune : امسح الصفحات الفاضية اللي محدش كتب فيها نص}';

    protected $description = 'توليد صفحات الهبوط (نوع × غرض × منطقة) من الوحدات المنشورة';

    public function handle(): int
    {
        $min = max(1, (int) $this->option('min'));

        $tally = $this->tally();
        $locations = Location::whereIn('id', array_filter(array_column($tally, 'location_id')))->get()->keyBy('id');

        $created = 0;
        $updated = 0;
        $kept = [];

        foreach ($tally as $combo) {
            if ($combo['units'] < $min) {
                continue;
            }

            $page = LandingPage::firstOrNew([
                'type' => $combo['type'],
                'purpose' => $combo['purpose'],
                'location_id' => $combo['location_id'],
            ]);

            if (! $page->exists) {
                // $base مش $baseEn: Str::slug بالإنجليزي بيبلع أي حروف عربية،
                // فمنطقة مالهاش اسم إنجليزي كان رابطها هيطلع مبتور
                $page->slug = LandingPage::buildSlug(
                    LandingPage::slugFor($combo['type'], $combo['purpose'], $locations->get($combo['location_id']))
                );
                $created++;
            } else {
                $updated++;
            }

            $page->units_count = $combo['units'];
            $page->is_active = true;
            $page->save();

            $kept[] = $page->id;
        }

        [$off, $pruned] = $this->retire($kept, $min);

        $this->info(sprintf(
            '  صفحات هبوط: %d جديدة · %d محدّثة · %d اتوقفت%s',
            $created,
            $updated,
            $off,
            $pruned ? " · {$pruned} اتمسحت" : '',
        ));

        return self::SUCCESS;
    }

    /**
     * التركيبات اللي عندها وحدات — بتتحسب في الذاكرة مرة واحدة بدل
     * استعلام لكل تركيبة محتملة (النوع × الغرض × المنطقة بتكبر بسرعة).
     *
     * @return array<string, array{type: ?string, purpose: ?string, location_id: ?int, units: int}>
     */
    private function tally(): array
    {
        $tally = [];

        $add = function (?string $type, ?string $purpose, ?int $areaId) use (&$tally) {
            $key = $type.'|'.$purpose.'|'.$areaId;

            $tally[$key] ??= ['type' => $type, 'purpose' => $purpose, 'location_id' => $areaId, 'units' => 0];
            $tally[$key]['units']++;
        };

        foreach (Property::published()->with('compound')->get() as $property) {
            /** @var Property $property */
            $type = isset(Property::TYPE_PLURALS[$property->type]) ? $property->type : null;
            $purpose = isset(LandingPage::PURPOSES[$property->purpose]) ? $property->purpose : null;

            // نفس الوراثة اللي في الكارت وفي فلتر المنطقة
            $areaId = $property->location_id ?: $property->compound?->location_id;

            if ($type && $purpose) {
                $add($type, $purpose, null);
            }

            if ($areaId) {
                $add(null, null, $areaId);
            }

            if ($type && $purpose && $areaId) {
                $add($type, $purpose, $areaId);
            }
        }

        return $tally;
    }

    /**
     * الصفحات اللي مش في التوليد الحالي: بتتعدّ لوحدها (ممكن تكون
     * تركيبة الأدمن عملها بإيده)، واللي قلّت عن الحد بتتوقف.
     *
     * @param  int[]  $kept
     * @return array{0: int, 1: int}
     */
    private function retire(array $kept, int $min): array
    {
        $off = 0;
        $pruned = 0;

        $stale = LandingPage::query()
            ->when($kept, fn ($q) => $q->whereNotIn('id', $kept))
            ->with('location')
            ->get();

        foreach ($stale as $page) {
            /** @var LandingPage $page */
            $units = Catalog::countProperties($page->filters());

            if ($units >= $min) {
                $page->update(['units_count' => $units, 'is_active' => true]);

                continue;
            }

            // المسح للفاضي بس: نص المحرّر مايضيعش لأن الوحدات خلصت مؤقتًا
            if ($this->option('prune') && $this->untouched($page)) {
                $page->delete();
                $pruned++;

                continue;
            }

            $page->update(['units_count' => 0, 'is_active' => false]);
            $off++;
        }

        return [$off, $pruned];
    }

    /** الصفحة اللي كل نصوصها مولّدة — مفيش حاجة تتخسر لو اتمسحت */
    private function untouched(LandingPage $page): bool
    {
        foreach (['h1', 'h1_en', 'intro', 'intro_en', 'meta_title', 'meta_title_en', 'meta_description', 'meta_description_en'] as $field) {
            if (filled($page->{$field})) {
                return false;
            }
        }

        return true;
    }
}
