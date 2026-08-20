<?php

namespace Modules\Locations\Models;

use App\Support\Bilingual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Properties\Models\Property;

class Location extends Model
{
    use Bilingual;

    protected $fillable = ['name', 'name_en', 'note', 'note_en', 'image', 'sort', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sort' => 'integer'];

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /** شكل الكارت في الموقع العام */
    public function toCard(string $locale): array
    {
        return [
            'id' => $this->id,
            'name' => $this->t('name', $locale),
            'note' => $this->t('note', $locale) ?? '',
            'image' => $this->image ?: '/images/demo/area-1.jpg',
        ];
    }
}
