<?php

namespace Database\Seeders;

use App\Support\DemoContent;
use Illuminate\Database\Seeder;
use Modules\Compounds\Models\Compound;
use Modules\Developers\Models\Developer;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;

/**
 * ينقل بيانات DemoContent التجريبية للجداول الحقيقية.
 * idempotent — بيستخدم updateOrCreate فتقدر تعيد تشغيله من غير تكرار.
 */
class CatalogSeeder extends Seeder
{
    /** الوحدة التجريبية دي تبع أنهي مشروع — المفتاح هو كود الوحدة */
    private const COMPOUND_OF = [
        'XH-1001' => 'النخيل هايتس',
        'XH-1002' => 'سيلين ريزيدنس',
        'XH-1003' => 'مارينا ووك',
        'XH-1004' => 'جرين أفينيو',
        'XH-1005' => 'سيلين ريزيدنس',
        'XH-1006' => 'كابيتال سكوير',
        'XH-1009' => 'النخيل هايتس',
    ];

    public function run(): void
    {
        $arProps = DemoContent::properties('ar');
        $enProps = DemoContent::properties('en');
        $arComps = DemoContent::compounds('ar');
        $enComps = DemoContent::compounds('en');
        $arAreas = DemoContent::areas('ar');
        $enAreas = DemoContent::areas('en');
        $arPropDetails = DemoContent::propertyDetails('ar');
        $enPropDetails = DemoContent::propertyDetails('en');
        $arCompDetails = DemoContent::compoundDetails('ar');
        $enCompDetails = DemoContent::compoundDetails('en');
        $arDevProfiles = DemoContent::developerProfiles('ar');
        $enDevProfiles = DemoContent::developerProfiles('en');
        $arAreaProfiles = DemoContent::areaProfiles('ar');
        $enAreaProfiles = DemoContent::areaProfiles('en');

        // ---------- المناطق ----------
        $locations = [];
        foreach ($arAreas as $i => $a) {
            $profile = $arAreaProfiles[$a['name']] ?? [];
            $profileEn = $enAreaProfiles[$a['name']] ?? [];
            $existingLoc = Location::where('name', $a['name'])->first();

            $locations[$a['name']] = Location::updateOrCreate(
                ['name' => $a['name']],
                [
                    'name_en' => $enAreas[$i]['name'] ?? null,
                    'slug' => $existingLoc?->slug ?: Location::buildSlug($a['name'], $enAreas[$i]['name'] ?? null, $existingLoc?->id),
                    'note' => $a['note'],
                    'note_en' => $enAreas[$i]['note'] ?? null,
                    'about' => $profile['about'] ?? null,
                    'about_en' => $profileEn['about'] ?? null,
                    'image' => $a['image'],
                    'cover' => $profile['cover'] ?? null,
                    'is_featured' => (bool) ($profile['featured'] ?? false),
                    'sort' => $i,
                ],
            );
        }

        // مناطق إضافية ظاهرة في العقارات بس
        foreach (array_unique(array_column($arProps, 'area')) as $name) {
            if (! isset($locations[$name])) {
                $existingLoc = Location::where('name', $name)->first();

                $locations[$name] = Location::updateOrCreate(['name' => $name], [
                    'slug' => $existingLoc?->slug ?: Location::buildSlug($name, null, $existingLoc?->id),
                    'sort' => 90,
                ]);
            }
        }

        // ---------- المطوّرون ----------
        $developers = [];
        foreach ($arComps as $i => $c) {
            $name = $c['developer'];

            if (! isset($developers[$name])) {
                $profile = $arDevProfiles[$name] ?? [];
                $profileEn = $enDevProfiles[$name] ?? [];
                $existingDev = Developer::where('name', $name)->first();
                $nameEn = $enComps[$i]['developer'] ?? null;

                $developers[$name] = Developer::updateOrCreate(
                    ['name' => $name],
                    [
                        'name_en' => $nameEn,
                        'slug' => $existingDev?->slug ?: Developer::buildSlug($name, $nameEn, $existingDev?->id),
                        'about' => $profile['about'] ?? null,
                        'about_en' => $profileEn['about'] ?? null,
                        'cover' => $profile['cover'] ?? null,
                        'website' => ($profile['website'] ?? '') ?: null,
                        'founded_year' => $profile['founded'] ?? null,
                        'headquarters' => $profile['hq'] ?? null,
                        'headquarters_en' => $profileEn['hq'] ?? null,
                        'sort' => count($developers),
                    ],
                );
            }
        }

        // ---------- الكمبوندات ----------
        $compounds = [];
        foreach ($arComps as $i => $c) {
            $en = $enComps[$i] ?? [];

            $detail = $arCompDetails[$i] ?? [];
            $detailEn = $enCompDetails[$i] ?? [];
            // الـ id بيتمرّر لـ buildSlug عشان إعادة التشغيل متولّدش -2 و-3
            $existing = Compound::where('name', $c['name'])->first();

            $compounds[$c['name']] = Compound::updateOrCreate(
                ['name' => $c['name']],
                [
                    'name_en' => $en['name'] ?? null,
                    'slug' => $existing?->slug ?: Compound::buildSlug($c['name'], $en['name'] ?? null, $existing?->id),
                    'features' => $detail['features'] ?? null,
                    'features_en' => $detailEn['features'] ?? null,
                    'gallery' => implode("\n", $detail['gallery'] ?? []),
                    'developer_id' => $developers[$c['developer']]->id ?? null,
                    'location_id' => $locations[$c['area']]->id ?? null,
                    'description' => $c['desc'] ?? null,
                    'description_en' => $en['desc'] ?? null,
                    'starting_price' => $c['starting'],
                    'down_payment' => $c['down'],
                    'installment_years' => $c['years'],
                    'installment_years_en' => $en['years'] ?? null,
                    'delivery' => $c['delivery'] ?? null,
                    'image' => $c['image'],
                    'is_new' => $c['new'],
                    'sort' => $i,
                ],
            );
        }

        // ---------- العقارات ----------
        foreach ($arProps as $i => $p) {
            $en = $enProps[$i] ?? [];
            $detail = $arPropDetails[$p['ref']] ?? [];
            $detailEn = $enPropDetails[$p['ref']] ?? [];
            $compoundName = self::COMPOUND_OF[$p['ref']] ?? null;
            $existing = Property::where('ref', $p['ref'])->first();

            Property::updateOrCreate(
                ['ref' => $p['ref']],
                [
                    'title' => $p['title'],
                    'title_en' => $en['title'] ?? null,
                    'slug' => $existing?->slug ?: Property::buildSlug($p['title'], $en['title'] ?? null, $existing?->id),
                    'description' => $detail['desc'] ?? null,
                    'description_en' => $detailEn['desc'] ?? null,
                    'features' => $detail['features'] ?? null,
                    'features_en' => $detailEn['features'] ?? null,
                    'gallery' => implode("\n", $detail['gallery'] ?? []),
                    'location_id' => $locations[$p['area']]->id ?? null,
                    'compound_id' => $compoundName ? ($compounds[$compoundName]->id ?? null) : null,
                    'purpose' => $p['purpose'] === 'إيجار' ? 'rent' : 'sale',
                    'type' => $this->guessType($p['title']),
                    'price' => $p['price'],
                    'price_en' => $en['price'] ?? null,
                    'beds' => $p['beds'],
                    'baths' => $p['baths'],
                    'size' => $p['size'],
                    'image' => $p['image'],
                    'sort' => $i,
                ],
            );
        }

        $this->command?->info(sprintf(
            '  مناطق: %d · مطوّرون: %d · كمبوندات: %d · عقارات: %d',
            Location::count(), Developer::count(), Compound::count(), Property::count(),
        ));
    }

    /** نوع العقار من عنوانه — الأنواع مصدرها Property::TYPES */
    private function guessType(string $title): string
    {
        foreach (array_keys(Property::TYPES) as $type) {
            // "مكتب إداري" بيتطابق مع عنوان فيه "مكتب إداري"، و"شقة" مع "شقة"
            if (str_contains($title, $type)) {
                return $type;
            }
        }

        return array_key_first(Property::TYPES);
    }
}
