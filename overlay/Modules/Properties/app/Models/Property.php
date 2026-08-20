<?php

namespace Modules\Properties\Models;

use App\Support\Bilingual;
use App\Support\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Compounds\Models\Compound;
use Modules\Locations\Models\Location;

/**
 * @property int $id
 * @property string $title
 * @property string|null $title_en
 * @property string|null $slug
 * @property int|null $location_id
 * @property int|null $compound_id
 * @property string $purpose
 * @property string|null $type
 * @property string|null $description
 * @property string|null $description_en
 * @property string|null $features
 * @property string|null $features_en
 * @property string|null $price
 * @property string|null $price_en
 * @property int $beds
 * @property int $baths
 * @property int $size
 * @property string|null $ref
 * @property string|null $image
 * @property string|null $gallery
 * @property int $sort
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Location|null $location
 * @property-read Compound|null $compound
 */
class Property extends Model
{
    use Bilingual, Sluggable;

    /**
     * أنواع العقارات — مصدر واحد للأدمن ولفلتر البحث في الهيرو،
     * عشان القيمة اللي بتتخزّن هي نفسها اللي البحث بيدوّر بيها.
     */
    public const TYPES = [
        'شقة' => 'Apartment',
        'دوبلكس' => 'Duplex',
        'بنتهاوس' => 'Penthouse',
        'استوديو' => 'Studio',
        'فيلا' => 'Villa',
        'تاون هاوس' => 'Townhouse',
        'توين هاوس' => 'Twin house',
        'شاليه' => 'Chalet',
        'مكتب إداري' => 'Office',
        'محل تجاري' => 'Retail',
        'عيادة' => 'Clinic',
    ];

    protected $fillable = [
        'title', 'title_en', 'slug', 'location_id', 'compound_id', 'purpose', 'type',
        'description', 'description_en', 'features', 'features_en',
        'price', 'price_en', 'beds', 'baths', 'size', 'ref', 'image', 'gallery', 'sort', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'beds' => 'integer', 'baths' => 'integer', 'size' => 'integer', 'sort' => 'integer',
    ];

    protected static function slugFallback(): string
    {
        return 'property';
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function compound(): BelongsTo
    {
        return $this->belongsTo(Compound::class);
    }

    /** النوع باللغة المطلوبة — متخزّن بالعربي دايمًا */
    public function typeLabel(string $locale): string
    {
        return $locale === 'en'
            ? (self::TYPES[$this->type] ?? (string) $this->type)
            : (string) $this->type;
    }

    public function toCard(string $locale): array
    {
        $ar = $locale !== 'en';

        return [
            'id' => $this->id,
            'slug' => $this->slug ?? '',
            'title' => $this->t('title', $locale),
            'area' => $this->location?->t('name', $locale) ?? '',
            'purpose' => $this->purpose === 'rent' ? ($ar ? 'إيجار' : 'Rent') : ($ar ? 'بيع' : 'Sale'),
            'type' => $this->typeLabel($locale),
            'price' => $this->t('price', $locale) ?? '',
            'beds' => (int) $this->beds,
            'baths' => (int) $this->baths,
            'size' => (int) $this->size,
            'ref' => $this->ref ?? '',
            'image' => $this->image ?: '/images/demo/property-1.jpg',
        ];
    }

    /** بيانات صفحة العقار — الكارت + التفاصيل الكاملة */
    public function toDetail(string $locale): array
    {
        $main = $this->image ?: '/images/demo/property-1.jpg';

        return $this->toCard($locale) + [
            'description' => $this->t('description', $locale) ?? '',
            'features' => $this->tLines('features', $locale),
            // الصورة الرئيسية أول المعرض دايمًا، من غير تكرار
            'gallery' => array_values(array_unique([$main, ...self::lines($this->gallery)])),
            'compound' => $this->compound && $this->compound->is_active ? [
                'name' => $this->compound->t('name', $locale),
                'slug' => $this->compound->slug ?? '',
                'developer' => $this->compound->developer?->t('name', $locale) ?? '',
                'delivery' => $this->compound->delivery ?? '',
            ] : null,
        ];
    }
}
