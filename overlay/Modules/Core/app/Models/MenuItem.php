<?php

namespace Modules\Core\Models;

use App\Support\Bilingual;
use App\Support\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * لينكات قوايم الموقع (الهيدر والفوتر) — بدل ما تكون مكتوبة في SiteLayout.
 * القراءة cached للأبد وبتتمسح تلقائيًا عند أي حفظ أو حذف.
 */
/**
 * @property int $id
 * @property string $location
 * @property int|null $parent_id
 * @property string $label
 * @property string|null $label_en
 * @property string|null $url
 * @property bool $new_tab
 * @property int $sort
 * @property bool $is_active
 */
class MenuItem extends Model
{
    use Bilingual, LogsActivity;

    protected $fillable = ['location', 'parent_id', 'label', 'label_en', 'url', 'new_tab', 'sort', 'is_active'];

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

    /**
     * كل اللينكات المفعّلة مجمّعة بالمكان، والأبناء تحت أبوهم.
     * العنصر الأب من غير url بيبقى عنوان قايمة منسدلة بس.
     */
    public static function nav(string $locale): array
    {
        $rows = Cache::rememberForever(
            'menu.items',
            fn () => static::query()->where('is_active', true)->orderBy('sort')->orderBy('id')->get(),
        );

        $link = fn (self $i, array $children = []) => [
            'label' => $i->t('label', $locale),
            'url' => $i->url ?? '',
            'external' => str_starts_with((string) $i->url, 'http'),
            'newTab' => (bool) $i->new_tab,
            'children' => $children,
        ];

        $nav = [];

        foreach (array_keys(self::LOCATIONS) as $location) {
            $inPlace = $rows->where('location', $location);

            $nav[$location] = $inPlace
                ->whereNull('parent_id')
                ->map(fn (self $i) => $link(
                    $i,
                    $inPlace->where('parent_id', $i->id)->map(fn (self $c) => $link($c))->values()->all(),
                ))
                // عنصر أب من غير رابط ولا أبناء مالوش لازمة
                ->filter(fn (array $item) => $item['url'] !== '' || $item['children'] !== [])
                ->values()
                ->all();
        }

        return $nav;
    }
}
