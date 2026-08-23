<?php

namespace Modules\Properties\Models;

use App\Models\User;
use App\Support\Bilingual;
use App\Support\LogsActivity;
use App\Support\SharedSlugSpace;
use App\Support\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Compounds\Models\Compound;
use Modules\Developers\Models\Developer;
use Modules\Leads\Models\Lead;
use Modules\Locations\Models\Location;
use Modules\Marketing\Models\FeaturedAd;

/**
 * @property int $id
 * @property string $title
 * @property string|null $title_en
 * @property string|null $slug
 * @property int|null $location_id
 * @property int|null $compound_id
 * @property int|null $developer_id
 * @property int|null $owner_id
 * @property string $purpose
 * @property string|null $type
 * @property int|null $price_amount
 * @property int|null $down_payment
 * @property int|null $monthly_installment
 * @property int|null $installment_years
 * @property int|null $delivery_year
 * @property string|null $finishing
 * @property string|null $floor
 * @property bool $has_garden
 * @property bool $has_roof
 * @property bool $has_dressing_room
 * @property string $status
 * @property string|null $rejection_reason
 * @property Carbon|null $published_at
 * @property bool $is_featured
 * @property int $views_count
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
 * @property-read Developer|null $developer
 * @property-read User|null $owner
 * @property-read int|null $leads_count
 * @property-read int|null $favorited_by_count
 */
class Property extends Model
{
    use Bilingual, LogsActivity, Sluggable;

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

    /**
     * صيغة الجمع لكل نوع — بتغذّي صفحات الهبوط البرمجية:
     * slug = كلمة الرابط (/properties/apartments-for-sale)، و ar/en = العنوان.
     * مصدر واحد عشان الرابط والعنوان مايختلفوش.
     */
    public const TYPE_PLURALS = [
        'شقة' => ['slug' => 'apartments', 'ar' => 'شقق', 'en' => 'Apartments'],
        'دوبلكس' => ['slug' => 'duplexes', 'ar' => 'دوبلكس', 'en' => 'Duplexes'],
        'بنتهاوس' => ['slug' => 'penthouses', 'ar' => 'بنتهاوس', 'en' => 'Penthouses'],
        'استوديو' => ['slug' => 'studios', 'ar' => 'استوديوهات', 'en' => 'Studios'],
        'فيلا' => ['slug' => 'villas', 'ar' => 'فيلات', 'en' => 'Villas'],
        'تاون هاوس' => ['slug' => 'townhouses', 'ar' => 'تاون هاوس', 'en' => 'Townhouses'],
        'توين هاوس' => ['slug' => 'twin-houses', 'ar' => 'توين هاوس', 'en' => 'Twin houses'],
        'شاليه' => ['slug' => 'chalets', 'ar' => 'شاليهات', 'en' => 'Chalets'],
        'مكتب إداري' => ['slug' => 'offices', 'ar' => 'مكاتب إدارية', 'en' => 'Offices'],
        'محل تجاري' => ['slug' => 'shops', 'ar' => 'محلات تجارية', 'en' => 'Shops'],
        'عيادة' => ['slug' => 'clinics', 'ar' => 'عيادات', 'en' => 'Clinics'],
    ];

    /**
     * الأنواع التجارية — الباقي سكني.
     * التقسيمة دي بتغذّي /properties/commercial وفلتر «نوع العقار»،
     * ومصدرها واحد عشان مايحصلش اختلاف بين الفلتر والصفحة.
     */
    public const COMMERCIAL_TYPES = ['مكتب إداري', 'محل تجاري', 'عيادة'];

    /** العملة — مكان واحد بدل ما تتكرر في العرض */
    public const CURRENCY = 'EGP';

    /** بادئة الكود المرجعي اللي بيتولّد عند الاعتماد */
    public const REF_PREFIX = 'BP';

    /** مستويات التشطيب — مفتاح ثابت وترجمة للعرض */
    public const FINISHING = [
        'none' => ['ar' => 'بدون تشطيب', 'en' => 'Unfinished'],
        'semi' => ['ar' => 'نص تشطيب', 'en' => 'Semi-finished'],
        'full' => ['ar' => 'تشطيب كامل', 'en' => 'Fully finished'],
        'furnished' => ['ar' => 'مفروش', 'en' => 'Furnished'],
        'flexi' => ['ar' => 'فليكسي', 'en' => 'Flexi'],
    ];

    /**
     * حالة المراجعة. الوحدة مبتظهرش للزوار غير لما تبقى published
     * و is_active كمان — دي حالة المراجعة، وده «معروض» من صاحبه.
     */
    public const STATUSES = [
        'draft' => ['label' => 'مسودة', 'tone' => 'muted'],
        'pending' => ['label' => 'في انتظار المراجعة', 'tone' => 'warn'],
        'published' => ['label' => 'منشور', 'tone' => 'success'],
        'rejected' => ['label' => 'مرفوض', 'tone' => 'danger'],
        'sold' => ['label' => 'اتباع', 'tone' => 'muted'],
        'rented' => ['label' => 'اتأجّر', 'tone' => 'muted'],
    ];

    /** الحالات اللي الوحدة فيها لسه معروضة للبيع/الإيجار */
    public const OPEN_STATUSES = ['draft', 'pending', 'published', 'rejected'];

    public const CATEGORIES = [
        'residential' => ['ar' => 'سكني', 'en' => 'Residential'],
        'commercial' => ['ar' => 'تجاري', 'en' => 'Commercial'],
    ];

    protected $fillable = [
        'title', 'title_en', 'slug', 'location_id', 'compound_id', 'developer_id', 'owner_id', 'purpose', 'type',
        'description', 'description_en', 'features', 'features_en',
        'price', 'price_en', 'price_amount', 'down_payment', 'monthly_installment',
        'installment_years', 'delivery_year', 'finishing', 'floor',
        'has_garden', 'has_roof', 'has_dressing_room',
        'beds', 'baths', 'size', 'ref', 'image', 'gallery', 'sort', 'is_active',
        'status', 'rejection_reason', 'published_at', 'is_featured', 'views_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'has_garden' => 'boolean',
        'has_roof' => 'boolean',
        'has_dressing_room' => 'boolean',
        'published_at' => 'datetime',
        'beds' => 'integer', 'baths' => 'integer', 'size' => 'integer', 'sort' => 'integer',
        'price_amount' => 'integer', 'down_payment' => 'integer',
        'monthly_installment' => 'integer', 'installment_years' => 'integer',
        'delivery_year' => 'integer', 'views_count' => 'integer',
    ];

    /**
     * الديفولت زي ديفولت العمود: الصف اللي بيتعمل من كود (استيراد، سيدر،
     * أمر) بيبقى منشور. المراجعة بتتفرض في طبقة اللوحة على غير الأدمن —
     * PropertyAdminController::applyModeration — مش هنا، عشان الاستيراد
     * ما يقفش والوسيط برضه ما يقدرش ينشر بنفسه.
     */
    protected $attributes = [
        'status' => 'published',
        'is_active' => true,
    ];

    protected static function slugFallback(): string
    {
        return 'property';
    }

    /**
     * الوحدة وصفحة الهبوط بيتشاركوا /properties/{slug}، فالرابط لازم
     * يبقى فريد على الاتنين — راجع SharedSlugSpace.
     */
    protected static function slugTaken(string $slug, ?int $ignoreId): bool
    {
        return SharedSlugSpace::taken($slug, 'properties', $ignoreId);
    }

    /**
     * لحظة الاعتماد: الوحدة بتاخد كود مرجعي وتاريخ نشر.
     * في الموديل مش في الكنترولر عشان الاعتماد من أي مكان (أمر، استيراد،
     * سيدر) يطلّع نفس النتيجة.
     */
    protected static function booted(): void
    {
        static::saving(function (self $property) {
            if ($property->status !== 'published') {
                return;
            }

            if (blank($property->ref)) {
                $property->ref = self::nextRef();
            }

            $property->published_at ??= now();
        });
    }

    /** الكود اللي بعد أكبر رقم موجود بنفس البادئة */
    public static function nextRef(): string
    {
        $prefix = self::REF_PREFIX.'-';

        $last = static::query()
            ->where('ref', 'like', $prefix.'%')
            ->orderByRaw('length(ref) desc')
            ->orderByDesc('ref')
            ->value('ref');

        $number = (int) preg_replace('/\D+/', '', (string) $last);

        do {
            $ref = $prefix.str_pad((string) ++$number, 4, '0', STR_PAD_LEFT);
        } while (static::where('ref', $ref)->exists());

        return $ref;
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function compound(): BelongsTo
    {
        return $this->belongsTo(Compound::class);
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    /**
     * المطوّر الفعلي: بتاع الوحدة، وإلا بتاع الكمبوند.
     *
     * الوحدة جوه مشروع مش لازم تتكتب مطوّرها تاني — بتورّثه من الكمبوند،
     * ولو الكمبوند غيّر مطوّره الوحدات بتمشي وراه لوحدها. و developer_id
     * الصريح بيغلب دايمًا، عشان إعادة البيع والوحدات المستقلة.
     */
    public function resolvedDeveloper(): ?Developer
    {
        return $this->developer ?? $this->compound?->developer;
    }

    /** الوسيط أو الشركة صاحبة الوحدة — null يعني وحدة المنصّة */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** الطلبات اللي جات على الوحدة دي */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * المساحات الإعلانية على الوحدة دي
     *
     * @return HasMany<FeaturedAd, $this>
     */
    public function featuredAds(): HasMany
    {
        return $this->hasMany(FeaturedAd::class);
    }

    /** الحسابات اللي حافظة الوحدة */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    /**
     * زيارة واحدة للصفحة.
     *
     * على الـ query builder مش على الموديل عن قصد: كده مفيش أحداث
     * ولا updated_at بتتحرّك — وإلا كل زيارة كانت هتغيّر lastmod في
     * خريطة الموقع وتخلّي كل حاجة تبان اتعدّلت دلوقتي.
     */
    public static function recordView(int $id): void
    {
        DB::table('properties')->where('id', $id)->increment('views_count');
    }

    /**
     * تسجيل زيارة في «شوهدت مؤخرًا» للحساب المسجّل.
     * upsert مش insert: الزيارة التانية بتحدّث الوقت مش بتكرّر الصف.
     */
    public static function recordVisit(int $userId, int $propertyId): void
    {
        DB::table('recently_viewed')->upsert(
            [['user_id' => $userId, 'property_id' => $propertyId, 'viewed_at' => now()]],
            ['user_id', 'property_id'],
            ['viewed_at'],
        );
    }

    /** كل صور العرض بترتيبها — الرئيسية الأول */
    public function imagePaths(): array
    {
        return array_values(array_filter(array_unique([
            $this->image,
            ...self::lines($this->gallery),
        ])));
    }

    /**
     * الوحدات اللي الزائر بيشوفها: معتمدة من الإدارة ومعروضة من صاحبها.
     * أي استعلام عام لازم يعدّي من هنا — مصدر واحد بدل شرطين متكرّرين.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', 'published');
    }

    /** السعر للعرض: النص الاختياري بيغلب، وإلا بنركّب من الرقم */
    public function priceLabel(string $locale): string
    {
        if (filled($override = $this->t('price', $locale))) {
            return $override;
        }

        if (! $this->price_amount) {
            return $locale === 'en' ? 'Price on request' : 'السعر عند الاستعلام';
        }

        $amount = self::CURRENCY.' '.number_format($this->price_amount);

        return $this->purpose === 'rent'
            ? $amount.($locale === 'en' ? ' / mo' : ' / شهريًا')
            : $amount;
    }

    /** رقم بصيغة العرض — للمقدم والقسط */
    public static function money(?int $amount): string
    {
        return $amount ? self::CURRENCY.' '.number_format($amount) : '';
    }

    public function finishingLabel(string $locale): string
    {
        return self::FINISHING[$this->finishing][$locale === 'en' ? 'en' : 'ar'] ?? '';
    }

    /** أنواع القسم ده (بالعربي — زي ما هي متخزّنة) */
    public static function typesIn(string $category): array
    {
        return $category === 'commercial'
            ? self::COMMERCIAL_TYPES
            : array_values(array_diff(array_keys(self::TYPES), self::COMMERCIAL_TYPES));
    }

    /** سكني ولا تجاري */
    public function category(): string
    {
        return in_array($this->type, self::COMMERCIAL_TYPES, true) ? 'commercial' : 'residential';
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
            // نفس fallback المطوّر: الوحدة جوه مشروع بتورّث منطقته لو مش متكتبة عليها،
            // عشان اللي البحث بيلاقيه يبان على الكارت
            'area' => ($this->location ?? $this->compound?->location)?->t('name', $locale) ?? '',
            'purpose' => $this->purpose === 'rent' ? ($ar ? 'إيجار' : 'Rent') : ($ar ? 'بيع' : 'Sale'),
            'type' => $this->typeLabel($locale),
            'category' => $this->category(),
            'price' => $this->priceLabel($locale),
            'priceAmount' => (int) $this->price_amount,
            'featured' => (bool) $this->is_featured,
            'finishing' => $this->finishingLabel($locale),
            'beds' => (int) $this->beds,
            'baths' => (int) $this->baths,
            'size' => (int) $this->size,
            'ref' => $this->ref ?? '',
            'image' => $this->image ?: '/images/demo/property-1.jpg',
            'developer' => $this->resolvedDeveloper()?->t('name', $locale) ?? '',
        ];
    }

    /** بيانات صفحة العقار — الكارت + التفاصيل الكاملة */
    public function toDetail(string $locale): array
    {
        $main = $this->image ?: '/images/demo/property-1.jpg';

        return $this->toCard($locale) + [
            'description' => $this->t('description', $locale) ?? '',
            'floor' => $this->floor ?? '',
            'delivery' => $this->delivery_year ? (string) $this->delivery_year : '',
            'payment' => array_filter([
                'down' => self::money($this->down_payment),
                'monthly' => self::money($this->monthly_installment),
                'years' => $this->installment_years ? (string) $this->installment_years : '',
            ]),
            'amenities' => array_keys(array_filter([
                'garden' => $this->has_garden,
                'roof' => $this->has_roof,
                'dressing' => $this->has_dressing_room,
            ])),
            'features' => $this->tLines('features', $locale),
            // الصورة الرئيسية أول المعرض دايمًا، من غير تكرار
            'gallery' => array_values(array_unique([$main, ...self::lines($this->gallery)])),
            'views' => (int) $this->views_count,
            'compound' => $this->compound && $this->compound->is_active ? [
                'name' => $this->compound->t('name', $locale),
                'slug' => $this->compound->slug ?? '',
                // نفس مصدر الكارت — الوحدة المستقلة برضه بيبان مطوّرها
                'developer' => $this->resolvedDeveloper()?->t('name', $locale) ?? '',
                'delivery' => $this->compound->delivery ?? '',
            ] : null,
        ];
    }
}
