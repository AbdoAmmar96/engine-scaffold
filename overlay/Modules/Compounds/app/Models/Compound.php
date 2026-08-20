<?php

namespace Modules\Compounds\Models;

use App\Support\Bilingual;
use App\Support\Sluggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Developers\Models\Developer;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;

/**
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property string|null $slug
 * @property int|null $developer_id
 * @property int|null $location_id
 * @property string|null $description
 * @property string|null $description_en
 * @property string|null $features
 * @property string|null $features_en
 * @property string|null $starting_price
 * @property string|null $down_payment
 * @property string|null $installment_years
 * @property string|null $installment_years_en
 * @property string|null $delivery
 * @property string|null $image
 * @property string|null $gallery
 * @property bool $is_new
 * @property int $sort
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Developer|null $developer
 * @property-read Location|null $location
 * @property-read Collection<int, Property> $properties
 */
class Compound extends Model
{
    use Bilingual, Sluggable;

    protected $fillable = [
        'name', 'name_en', 'slug', 'developer_id', 'location_id', 'description', 'description_en',
        'features', 'features_en',
        'starting_price', 'down_payment', 'installment_years', 'installment_years_en',
        'delivery', 'image', 'gallery', 'is_new', 'sort', 'is_active',
    ];

    protected $casts = ['is_new' => 'boolean', 'is_active' => 'boolean', 'sort' => 'integer'];

    protected static function slugFallback(): string
    {
        return 'compound';
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /** نفس شكل الـ props اللي الفرونت متوقعه */
    public function toCard(string $locale): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug ?? '',
            'name' => $this->t('name', $locale),
            'developer' => $this->developer?->t('name', $locale) ?? '',
            'area' => $this->location?->t('name', $locale) ?? '',
            'desc' => $this->t('description', $locale) ?? '',
            'starting' => $this->starting_price ?? '',
            'down' => $this->down_payment ?? '',
            'years' => $this->t('installment_years', $locale) ?? '',
            'delivery' => $this->delivery ?? '',
            'new' => (bool) $this->is_new,
            'image' => $this->image ?: '/images/demo/compound-1.jpg',
        ];
    }

    /** بيانات صفحة الكمبوند — الكارت + المميزات والمعرض */
    public function toDetail(string $locale): array
    {
        $main = $this->image ?: '/images/demo/compound-1.jpg';

        return $this->toCard($locale) + [
            'features' => $this->tLines('features', $locale),
            'gallery' => array_values(array_unique([$main, ...self::lines($this->gallery)])),
        ];
    }
}
