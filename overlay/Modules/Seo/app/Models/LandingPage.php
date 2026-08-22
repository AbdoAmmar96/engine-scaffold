<?php

namespace Modules\Seo\Models;

use App\Support\Bilingual;
use App\Support\SharedSlugSpace;
use App\Support\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;

/**
 * صفحة هبوط برمجية: تركيبة (نوع × غرض × منطقة) بتتحوّل لصفحة نتايج
 * ليها عنوان ونص وميتا خاصين بيها.
 *
 * كل النصوص اختيارية: الفاضي بيتولّد من أبعاد الصفحة نفسها، والمكتوب
 * بيغلب. يعني الصفحة بتشتغل من أول ما الأمر يعملها، والمحرّر بيحسّنها
 * بعدين من غير ما حاجة تتعطّل في النص.
 *
 * @property int $id
 * @property string $slug
 * @property string|null $type
 * @property string|null $purpose
 * @property int|null $location_id
 * @property string|null $h1
 * @property string|null $h1_en
 * @property string|null $intro
 * @property string|null $intro_en
 * @property string|null $meta_title
 * @property string|null $meta_title_en
 * @property string|null $meta_description
 * @property string|null $meta_description_en
 * @property int $units_count
 * @property int $sort
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Location|null $location
 */
class LandingPage extends Model
{
    use Bilingual, Sluggable;

    protected $table = 'seo_landing_pages';

    protected $fillable = [
        'slug', 'type', 'purpose', 'location_id',
        'h1', 'h1_en', 'intro', 'intro_en',
        'meta_title', 'meta_title_en', 'meta_description', 'meta_description_en',
        'units_count', 'sort', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'units_count' => 'integer',
        'sort' => 'integer',
    ];

    protected $attributes = ['is_active' => true];

    /** الغرض: كلمة الرابط + العنوان بالعربي والإنجليزي */
    public const PURPOSES = [
        'sale' => ['slug' => 'sale', 'ar' => 'للبيع', 'en' => 'for sale'],
        'rent' => ['slug' => 'rent', 'ar' => 'للإيجار', 'en' => 'for rent'],
    ];

    protected static function slugFallback(): string
    {
        return 'landing';
    }

    /** الصفحة والوحدة بيتشاركوا /properties/{slug} — راجع SharedSlugSpace */
    protected static function slugTaken(string $slug, ?int $ignoreId): bool
    {
        return SharedSlugSpace::taken($slug, 'seo_landing_pages', $ignoreId);
    }

    protected function slugSource(): array
    {
        return [$this->heading('ar'), $this->headingParts('en')['plain']];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * رابط التركيبة — إنجليزي دايمًا لأن النسختين العربية والإنجليزية
     * بيتشاركوا نفس المسار (‏Seo::pathWithoutLocale بيبني hreflang عليه).
     * لو الرابط اتغيّر بلغة الصفحة، الـ canonical والـ alternates بيتكسروا.
     */
    public static function slugFor(?string $type, ?string $purpose, ?Location $location): string
    {
        $parts = [];

        if ($type && isset(Property::TYPE_PLURALS[$type])) {
            $parts[] = Property::TYPE_PLURALS[$type]['slug'];
        }

        if ($purpose && isset(self::PURPOSES[$purpose])) {
            $parts[] = 'for-'.self::PURPOSES[$purpose]['slug'];
        }

        if ($parts === []) {
            $parts[] = 'properties';
        }

        if ($location?->slug) {
            $parts[] = 'in-'.$location->slug;
        }

        return implode('-', $parts);
    }

    /** الفلاتر اللي الصفحة مثبّتة عليها — بتتبعت لـ Catalog زي أي فلتر تاني */
    public function filters(): array
    {
        return array_filter([
            'type' => $this->type ?? '',
            'purpose' => $this->purpose ?? '',
            'location' => $this->location->name ?? '',
        ], fn ($v) => $v !== '');
    }

    /** العنوان — المكتوب بيغلب، وإلا مركّب من أبعاد الصفحة */
    public function heading(string $locale): string
    {
        return filled($written = $this->t('h1', $locale))
            ? $written
            : $this->headingParts($locale)['full'];
    }

    public function metaTitle(string $locale): string
    {
        return filled($written = $this->t('meta_title', $locale))
            ? $written
            : $this->heading($locale);
    }

    public function intro(string $locale): string
    {
        if (filled($written = $this->t('intro', $locale))) {
            return $written;
        }

        $p = $this->headingParts($locale);

        return $locale === 'en'
            ? "Looking for {$p['plain']}? This page lists only what is available right now — each unit shows its price, area, room count and payment plan, and you can narrow the results by price, area or finishing until you land on the right one."
            : "لو بتدوّر على {$p['plain']}، الصفحة دي بتجمع المتاح دلوقتي بس. كل وحدة مكتوب عليها سعرها ومساحتها وعدد غرفها ونظام السداد، وتقدر تفلتر بالسعر أو المساحة أو التشطيب لحد ما توصل للي يناسبك.";
    }

    public function metaDescription(string $locale): string
    {
        if (filled($written = $this->t('meta_description', $locale))) {
            return $written;
        }

        $p = $this->headingParts($locale);

        return $locale === 'en'
            ? "Browse {$p['plain']} with up-to-date prices, payment plans and stated delivery dates. Every unit carries a reference number."
            : "تصفّح {$p['plain']} بأسعار محدّثة وأنظمة سداد وتواريخ تسليم موضّحة، وكود مرجعي لكل وحدة.";
    }

    /**
     * أجزاء العنوان: full بيبدأ بحرف كبير للإنجليزي، و plain بيتحط
     * في نص جملة. الاتنين من نفس المصدر عشان مايختلفوش.
     *
     * @return array{full: string, plain: string}
     */
    public function headingParts(string $locale): array
    {
        $en = $locale === 'en';
        $words = [];

        $words[] = $this->type && isset(Property::TYPE_PLURALS[$this->type])
            ? Property::TYPE_PLURALS[$this->type][$en ? 'en' : 'ar']
            : ($en ? 'Properties' : 'عقارات');

        if ($this->purpose && isset(self::PURPOSES[$this->purpose])) {
            $words[] = self::PURPOSES[$this->purpose][$en ? 'en' : 'ar'];
        }

        if ($area = $this->location?->t('name', $locale)) {
            $words[] = ($en ? 'in ' : 'في ').$area;
        }

        $full = implode(' ', $words);

        // الإنجليزي بيتخزّن بحرف كبير في العنوان وصغير جوه الجملة
        return [
            'full' => $full,
            'plain' => $en ? lcfirst($full) : $full,
        ];
    }

    /** لينك الصفحة في قوائم «صفحات مشابهة» وخريطة الموقع */
    public function toLink(string $locale): array
    {
        return [
            'label' => $this->heading($locale),
            'url' => "/{$locale}/properties/{$this->slug}",
            'count' => $this->units_count,
        ];
    }
}
