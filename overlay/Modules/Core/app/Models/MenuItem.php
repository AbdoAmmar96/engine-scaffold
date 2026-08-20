<?php

namespace Modules\Core\Models;

use App\Support\Bilingual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * لينكات قوايم الموقع (الهيدر والفوتر) — بدل ما تكون مكتوبة في SiteLayout.
 * القراءة cached للأبد وبتتمسح تلقائيًا عند أي حفظ أو حذف.
 */
class MenuItem extends Model
{
    use Bilingual;

    protected $fillable = ['location', 'label', 'label_en', 'url', 'new_tab', 'sort', 'is_active'];

    protected $casts = ['new_tab' => 'boolean', 'is_active' => 'boolean', 'sort' => 'integer'];

    public const LOCATIONS = [
        'header' => 'القائمة العلوية',
        'footer' => 'قائمة الفوتر',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => static::flush());
        static::deleted(fn () => static::flush());
    }

    public static function flush(): void
    {
        Cache::forget('menu.items');
    }

    /** كل اللينكات المفعّلة مجمّعة بالمكان — الشكل اللي الفرونت متوقعه */
    public static function nav(string $locale): array
    {
        $rows = Cache::rememberForever(
            'menu.items',
            fn () => static::query()->where('is_active', true)->orderBy('sort')->orderBy('id')->get(),
        );

        $nav = [];

        foreach (array_keys(self::LOCATIONS) as $location) {
            $nav[$location] = $rows
                ->where('location', $location)
                ->map(fn (self $i) => [
                    'label' => $i->t('label', $locale),
                    'url' => $i->url,
                    'external' => str_starts_with($i->url, 'http'),
                    'newTab' => $i->new_tab,
                ])
                ->values()
                ->all();
        }

        return $nav;
    }
}
