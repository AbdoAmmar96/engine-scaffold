<?php

namespace Modules\Reviews\Models;

use App\Models\User;
use App\Support\Bilingual;
use App\Support\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Compounds\Models\Compound;
use Modules\Properties\Models\Property;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $property_id
 * @property int|null $compound_id
 * @property string $author
 * @property string|null $author_en
 * @property string|null $role
 * @property string|null $role_en
 * @property string $body
 * @property string|null $body_en
 * @property int $rating
 * @property string|null $avatar
 * @property string $source
 * @property string $status
 * @property int $sort
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * المفاتيح الأجنبية كلها nullable — الرأي ممكن يكون عن الخدمة عمومًا،
 * وممكن الأدمن يكون كاتبه بالإيد من غير حساب. من غير التصريح ده
 * larastan بيفترض إن العلاقة موجودة دايمًا وبيشتكي من `?->`.
 * @property-read User|null $user
 * @property-read Property|null $property
 * @property-read Compound|null $compound
 */
class Review extends Model
{
    use Bilingual, LogsActivity;

    /** جه منين — بيفرّق بين رأي عميل حقيقي ورأي الأدمن كتبه */
    public const SOURCES = [
        'site' => 'العميل كتبه من حسابه',
        'manual' => 'الأدمن كتبه',
        'google' => 'منقول من جوجل',
    ];

    public const STATUSES = [
        'pending' => 'تحت المراجعة',
        'published' => 'منشور',
        'rejected' => 'مرفوض',
    ];

    protected $fillable = [
        'user_id', 'property_id', 'compound_id', 'author', 'author_en',
        'role', 'role_en', 'body', 'body_en', 'rating', 'avatar',
        'source', 'status', 'sort', 'published_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'sort' => 'integer',
        'published_at' => 'datetime',
    ];

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function compound(): BelongsTo
    {
        return $this->belongsTo(Compound::class);
    }

    /** الرأي عن إيه — وحدة، مشروع، ولا الخدمة عمومًا */
    public function about(?string $locale = null): string
    {
        return $this->property?->t('title', $locale)
            ?? $this->compound?->t('name', $locale)
            ?? 'الخدمة عمومًا';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }

    /** الرأي بالشكل اللي الفرونت متوقعه */
    public function toCard(string $locale): array
    {
        return [
            'id' => $this->id,
            'author' => (string) $this->t('author', $locale),
            'role' => $this->t('role', $locale) ?: '',
            'body' => (string) $this->t('body', $locale),
            'rating' => $this->rating,
            'avatar' => $this->avatar ?: null,
            'date' => $this->published_at?->translatedFormat($locale === 'en' ? 'M Y' : 'F Y') ?? '',
        ];
    }
}
