<?php

namespace Modules\Developers\Models;

use App\Support\Bilingual;
use App\Support\LogsActivity;
use App\Support\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Compounds\Models\Compound;
use Modules\Properties\Models\Property;

/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property string|null $slug
 * @property string|null $about
 * @property string|null $about_en
 * @property string|null $logo
 * @property string|null $cover
 * @property string|null $website
 * @property string|null $founded_year
 * @property string|null $headquarters
 * @property string|null $headquarters_en
 * @property int $sort
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $compounds_count
 * @property-read int|null $properties_count
 */
class Developer extends Model
{
    use Bilingual, LogsActivity, Sluggable;

    protected $fillable = [
        'name', 'name_en', 'slug', 'about', 'about_en', 'logo', 'cover',
        'website', 'founded_year', 'headquarters', 'headquarters_en', 'sort', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'sort' => 'integer'];

    protected static function slugFallback(): string
    {
        return 'developer';
    }

    public function compounds(): HasMany
    {
        return $this->hasMany(Compound::class);
    }

    /**
     * الوحدات المكتوب عليها المطوّر مباشرة — إعادة البيع والوحدات المستقلة.
     * الوحدات اللي جوه مشاريعه بتتحسب من ناحية الكمبوند، فمش بتتعد هنا مرتين.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * كارت المطوّر في الموقع العام.
     *
     * الأعداد بتتقرا من withCount — الكنترولر لازم يحمّلها، وإلا بترجع صفر
     * بدل ما تعمل استعلام لكل صف.
     */
    public function toCard(string $locale): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug ?? '',
            'name' => $this->t('name', $locale),
            'about' => $this->t('about', $locale) ?? '',
            // فاضي = الواجهة بترسم أول حرف بدل ما تسيب مربع مكسور
            'logo' => $this->logo ?: '',
            'compounds' => (int) ($this->compounds_count ?? 0),
            'url' => $this->slug ? "/{$locale}/developers/{$this->slug}" : "/{$locale}/developers",
        ];
    }

    /** صفحة المطوّر */
    public function toDetail(string $locale): array
    {
        return $this->toCard($locale) + [
            'cover' => $this->cover ?: '/images/demo/bg-comps.jpg',
            'website' => $this->website ?: '',
            'founded' => $this->founded_year ?: '',
            'headquarters' => $this->t('headquarters', $locale) ?? '',
        ];
    }
}
