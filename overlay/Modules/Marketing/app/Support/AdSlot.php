<?php

namespace Modules\Marketing\Support;

use Modules\Compounds\Models\Compound;
use Modules\Marketing\Models\FeaturedAd;
use Modules\Properties\Models\Property;

/**
 * قراءة المساحات الإعلانية للعرض.
 *
 * كل كارت بيرجع ومعاه `adId` و`url` بتعدّي على راوت التتبّع — من غير كده
 * الضغطات مش بتتحسب والتسويق بيقيس المساحة بالإحساس.
 *
 * الإعلان اللي هدفه اتوقف (وحدة اتباعت أو مشروع اتقفل) بيتشال هنا مش
 * بيتعرض ويودّي 404 — الجدولة حاجة وحالة الهدف حاجة تانية.
 */
class AdSlot
{
    public static function at(string $position, string $locale, int $limit = 3): array
    {
        if (! isset(FeaturedAd::POSITIONS[$position])) {
            return [];
        }

        $ads = FeaturedAd::query()
            ->live()
            ->at($position)
            ->with(['property.location', 'property.compound.developer', 'compound.developer', 'compound.location'])
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->limit($limit * 2)
            ->get();

        $cards = [];

        foreach ($ads as $ad) {
            if (! $card = self::card($ad, $locale)) {
                continue;
            }

            $cards[] = $card;

            if (count($cards) >= $limit) {
                break;
            }
        }

        // الظهور بيتحسب على اللي اتعرض فعلًا بس
        FeaturedAd::countImpressions(array_column($cards, 'adId'));

        return $cards;
    }

    /** كارت الإعلان، أو null لو هدفه مش معروض دلوقتي */
    private static function card(FeaturedAd $ad, string $locale): ?array
    {
        $property = $ad->property;
        $compound = $ad->compound;

        if ($property instanceof Property && $property->is_active && $property->status === 'published') {
            return $property->toCard($locale) + [
                'adId' => $ad->id,
                'kind' => 'property',
                'url' => "/{$locale}/ads/{$ad->id}",
            ];
        }

        if ($compound instanceof Compound && $compound->is_active) {
            return $compound->toCard($locale) + [
                'adId' => $ad->id,
                'kind' => 'compound',
                'url' => "/{$locale}/ads/{$ad->id}",
            ];
        }

        return null;
    }

    /** وجهة الضغطة — الصفحة الحقيقية للوحدة أو المشروع */
    public static function target(FeaturedAd $ad, string $locale): ?string
    {
        if ($ad->property?->slug) {
            return "/{$locale}/properties/{$ad->property->slug}";
        }

        if ($ad->compound?->slug) {
            return "/{$locale}/compounds/{$ad->compound->slug}";
        }

        return null;
    }
}
