<?php

namespace Modules\Marketing\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Marketing\Models\FeaturedAd;
use Modules\Marketing\Support\AdSlot;

/**
 * تتبّع الضغطة.
 *
 * ريدايركت مش جافاسكربت عن قصد: الضغطة بتتحسب حتى لو الجافاسكربت واقع
 * أو الزائر فتح اللينك في تاب جديد، وبتشتغل مع العناكب والقارئ الصوتي زي
 * أي لينك عادي.
 */
class AdClickController extends Controller
{
    public function __invoke(string $locale, int $ad): RedirectResponse
    {
        $featured = FeaturedAd::with(['property', 'compound'])->find($ad);

        $target = $featured ? AdSlot::target($featured, $locale) : null;

        // إعلان اتمسح أو هدفه اتشال — بنودّي القايمة بدل 404
        if (! $target) {
            return redirect("/{$locale}/properties");
        }

        FeaturedAd::countClick($featured->id);

        return redirect($target);
    }
}
