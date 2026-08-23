<?php

namespace Modules\Marketing\Models;

use App\Models\User;
use App\Support\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Compounds\Models\Compound;
use Modules\Properties\Models\Property;

/**
 * مساحة إعلانية مجدولة.
 *
 * الفرق بينها وبين `properties.is_featured`:
 *   is_featured = «مميّز دائم» — بيتصدّر نتايج البحث، توجل يدوي.
 *   FeaturedAd  = مساحة في مكان محدّد (الرئيسية/النتايج/صفحة الوحدة)
 *                 لفترة ببداية ونهاية، وبتقفل نفسها لوحدها.
 * الاتنين شغالين مع بعض ومحدش بيلغي التاني.
 *
 * @property int $id
 * @property string $position
 * @property int|null $property_id
 * @property int|null $compound_id
 * @property int|null $requested_by
 * @property string $status
 * @property string|null $rejection_reason
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property int $priority
 * @property bool $is_active
 * @property int $impressions
 * @property int $clicks
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Property|null $property
 * @property-read Compound|null $compound
 * @property-read User|null $requester
 */
class FeaturedAd extends Model
{
    use LogsActivity;

    /** المواضع — المفتاح بيتخزّن والوصف بيظهر في اللوحة */
    public const POSITIONS = [
        'hero' => ['label' => 'شريط الرئيسية', 'note' => 'تحت الهيرو مباشرة في الصفحة الرئيسية'],
        'listing' => ['label' => 'أول النتايج', 'note' => 'مثبّت فوق نتايج البحث'],
        'sidebar' => ['label' => 'جانب صفحة الوحدة', 'note' => 'في صفحة أي وحدة تانية'],
    ];

    public const STATUSES = [
        'pending' => ['label' => 'في انتظار الموافقة', 'tone' => 'warn'],
        'approved' => ['label' => 'معتمد', 'tone' => 'success'],
        'rejected' => ['label' => 'مرفوض', 'tone' => 'danger'],
    ];

    protected $fillable = [
        'position', 'property_id', 'compound_id', 'requested_by',
        'status', 'rejection_reason', 'starts_at', 'ends_at',
        'priority', 'is_active', 'impressions', 'clicks',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'impressions' => 'integer',
        'clicks' => 'integer',
    ];

    protected $attributes = [
        'status' => 'approved',
        'is_active' => true,
        'position' => 'listing',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function compound(): BelongsTo
    {
        return $this->belongsTo(Compound::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * الإعلانات اللي المفروض تتعرض دلوقتي.
     *
     * التاريخ الفاضي معناه «مفتوح»: بداية فاضية = شغّال من دلوقتي،
     * نهاية فاضية = لحد ما حد يقفله. ده بيخلي الإعلان الدائم ممكن
     * من غير عمود زيادة.
     */
    public function scopeLive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    public function scopeAt(Builder $query, string $position): Builder
    {
        return $query->where('position', $position);
    }

    /** شغّال دلوقتي؟ — نفس منطق scopeLive بس على صف واحد */
    public function isLive(): bool
    {
        return $this->status === 'approved'
            && $this->is_active
            && (! $this->starts_at || $this->starts_at->isPast())
            && (! $this->ends_at || $this->ends_at->isFuture());
    }

    /** الحالة للعرض: المجدول والمنتهي مش نفس «المعتمد» */
    public function stateLabel(): array
    {
        if ($this->status !== 'approved') {
            return self::STATUSES[$this->status] ?? ['label' => $this->status, 'tone' => 'muted'];
        }

        if (! $this->is_active) {
            return ['label' => 'متوقّف', 'tone' => 'muted'];
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return ['label' => 'مجدول '.$this->starts_at->format('Y/m/d'), 'tone' => 'primary'];
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return ['label' => 'انتهى '.$this->ends_at->format('Y/m/d'), 'tone' => 'muted'];
        }

        return ['label' => 'شغّال دلوقتي', 'tone' => 'success'];
    }

    /** الوحدة أو المشروع اللي الإعلان عليه */
    public function subject(?string $locale = null): ?string
    {
        return $this->property?->t('title', $locale) ?? $this->compound?->t('name', $locale);
    }

    /**
     * عدّادات الأداء — على الـ query builder عشان مفيش أحداث ولا
     * updated_at بتتحرّك مع كل ظهور
     */
    public static function countImpressions(array $ids): void
    {
        if ($ids !== []) {
            DB::table('featured_ads')->whereIn('id', $ids)->increment('impressions');
        }
    }

    public static function countClick(int $id): void
    {
        DB::table('featured_ads')->where('id', $id)->increment('clicks');
    }

    /** نسبة الضغط — الرقم اللي التسويق بيقيس بيه المساحة */
    public function ctr(): float
    {
        return $this->impressions > 0 ? round($this->clicks / $this->impressions * 100, 1) : 0.0;
    }
}
