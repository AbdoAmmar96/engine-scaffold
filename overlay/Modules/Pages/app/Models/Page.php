<?php

namespace Modules\Pages\Models;

use App\Support\Bilingual;
use App\Support\LogsActivity;
use App\Support\ReservedSlugs;
use App\Support\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string|null $title_en
 * @property string|null $excerpt
 * @property string|null $excerpt_en
 * @property string|null $body
 * @property string|null $body_en
 * @property string|null $meta_title
 * @property string|null $meta_title_en
 * @property string|null $meta_description
 * @property string|null $meta_description_en
 * @property bool $is_indexable
 * @property int $sort
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Page extends Model
{
    use Bilingual, LogsActivity, Sluggable;

    protected $fillable = [
        'slug', 'title', 'title_en', 'excerpt', 'excerpt_en', 'body', 'body_en',
        'meta_title', 'meta_title_en', 'meta_description', 'meta_description_en',
        'is_indexable', 'sort', 'is_active',
    ];

    protected $casts = [
        'is_indexable' => 'boolean',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    protected static function slugFallback(): string
    {
        return 'page';
    }

    /**
     * الرابط محجوز لو صف تاني واخده **أو** لو فيه راوت بنفس الاسم.
     *
     * التوليد التلقائي بيلف على الدالة دي، فصفحة اسمها «اتصل بنا» بتاخد
     * `contact-2` بدل ما تاخد `contact` وتقعد ورا الراوت الموجود للأبد.
     */
    protected static function slugTaken(string $slug, ?int $ignoreId): bool
    {
        if (ReservedSlugs::taken($slug)) {
            return true;
        }

        return static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }

    /** العنوان اللي بيتعرض — مفيش fallback مولّد، الأدمن هو اللي بيكتبه */
    public function heading(?string $locale = null): string
    {
        return (string) $this->t('title', $locale);
    }

    public function metaTitle(?string $locale = null): string
    {
        return (string) ($this->t('meta_title', $locale) ?: $this->heading($locale));
    }

    public function metaDescription(?string $locale = null): ?string
    {
        // الوصف المكتوب بيغلب، وبعده أول سطر من المقدمة — أحسن من ميتا فاضية
        return $this->t('meta_description', $locale)
            ?: $this->t('excerpt', $locale);
    }

    /** الصفحة بالشكل اللي الفرونت متوقعه */
    public function toPage(string $locale): array
    {
        return [
            'title' => $this->heading($locale),
            'slug' => $this->slug,
            'excerpt' => $this->t('excerpt', $locale) ?: '',
            'body' => $this->t('body', $locale) ?: '',
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'updatedLabel' => $this->updated_at?->translatedFormat($locale === 'en' ? 'j M Y' : 'j F Y') ?? '',
        ];
    }
}
