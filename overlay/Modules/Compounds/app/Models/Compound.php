<?php

namespace Modules\Compounds\Models;

use App\Support\Bilingual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Developers\Models\Developer;
use Modules\Locations\Models\Location;

class Compound extends Model
{
    use Bilingual;

    protected $fillable = [
        'name', 'name_en', 'developer_id', 'location_id', 'description', 'description_en',
        'starting_price', 'down_payment', 'installment_years', 'installment_years_en',
        'delivery', 'image', 'is_new', 'sort', 'is_active',
    ];

    protected $casts = ['is_new' => 'boolean', 'is_active' => 'boolean', 'sort' => 'integer'];

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /** نفس شكل الـ props اللي الفرونت متوقعه */
    public function toCard(string $locale): array
    {
        return [
            'id' => $this->id,
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
}
