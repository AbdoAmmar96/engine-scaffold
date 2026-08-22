<?php

namespace Modules\Developers\Models;

use App\Support\Bilingual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Compounds\Models\Compound;
use Modules\Properties\Models\Property;

/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property string|null $about
 * @property string|null $about_en
 * @property string|null $logo
 * @property int $sort
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $compounds_count
 * @property-read int|null $properties_count
 */
class Developer extends Model
{
    use Bilingual;

    protected $fillable = ['name', 'name_en', 'about', 'about_en', 'logo', 'sort', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sort' => 'integer'];

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
     *
     * @return array{id:int, name:string, about:string, logo:string, compounds:int, url:string}
     */
    public function toCard(string $locale): array
    {
        return [
            'id' => $this->id,
            'name' => $this->t('name', $locale),
            'about' => $this->t('about', $locale) ?? '',
            // فاضي = الواجهة بترسم أول حرف بدل ما تسيب مربع مكسور
            'logo' => $this->logo ?: '',
            'compounds' => (int) ($this->compounds_count ?? 0),
            // بحث الكمبوندات بيغطي اسم المطوّر، فالرابط بيوصّل لمشاريعه
            'url' => "/{$locale}/compounds?q=".rawurlencode($this->name),
        ];
    }
}
