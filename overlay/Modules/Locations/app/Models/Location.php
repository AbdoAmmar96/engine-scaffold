<?php

namespace Modules\Locations\Models;

use App\Support\Bilingual;
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
 * @property string|null $note
 * @property string|null $note_en
 * @property string|null $about
 * @property string|null $about_en
 * @property string|null $image
 * @property string|null $cover
 * @property bool $is_featured
 * @property int $sort
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int|null $properties_count
 * @property-read int|null $compounds_count
 */
class Location extends Model
{
    use Bilingual, Sluggable;

    protected $fillable = [
        'name', 'name_en', 'slug', 'note', 'note_en', 'about', 'about_en',
        'image', 'cover', 'is_featured', 'sort', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean', 'is_featured' => 'boolean', 'sort' => 'integer'];

    protected static function slugFallback(): string
    {
        return 'area';
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function compounds(): HasMany
    {
        return $this->hasMany(Compound::class);
    }

    /** شكل الكارت في الموقع العام */
    public function toCard(string $locale): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug ?? '',
            'name' => $this->t('name', $locale),
            'note' => $this->t('note', $locale) ?? '',
            'image' => $this->image ?: '/images/demo/area-1.jpg',
            'url' => $this->slug ? "/{$locale}/areas/{$this->slug}" : "/{$locale}/properties",
        ];
    }

    /** صفحة المنطقة */
    public function toDetail(string $locale): array
    {
        return $this->toCard($locale) + [
            'about' => $this->t('about', $locale) ?? '',
            'cover' => $this->cover ?: ($this->image ?: '/images/demo/bg-props.jpg'),
        ];
    }
}
