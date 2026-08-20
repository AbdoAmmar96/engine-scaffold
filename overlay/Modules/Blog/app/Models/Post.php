<?php

namespace Modules\Blog\Models;

use App\Support\Bilingual;
use App\Support\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Bilingual, Sluggable;

    protected $fillable = [
        'title', 'title_en', 'slug', 'category', 'category_en', 'excerpt', 'excerpt_en',
        'body', 'body_en', 'image', 'author', 'published_at', 'sort', 'is_active',
    ];

    protected $casts = [
        'published_at' => 'date',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->where(fn ($s) => $s->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    protected static function slugFallback(): string
    {
        return 'post';
    }

    /** تقدير وقت القراءة بالدقايق */
    public function readMinutes(?string $locale = null): int
    {
        $body = strip_tags((string) $this->t('body', $locale));
        $words = count(array_filter(preg_split('/\s+/u', $body) ?: []));

        return max(1, (int) ceil($words / 180));
    }

    /** نفس شكل الـ props اللي الفرونت متوقعه */
    public function toCard(string $locale): array
    {
        return [
            'id' => $this->id,
            'title' => $this->t('title', $locale),
            'slug' => $this->slug,
            'category' => $this->t('category', $locale) ?? '',
            'excerpt' => $this->t('excerpt', $locale) ?? '',
            'image' => $this->image ?: '/images/demo/property-1.jpg',
            'author' => $this->author ?: '',
            'date' => $this->published_at?->translatedFormat($locale === 'en' ? 'j M Y' : 'j F Y') ?? '',
            // ISO للـ JSON-LD و<time> — التاريخ المنسّق فوق للعرض بس
            'publishedAt' => $this->published_at?->toIso8601String(),
            'read' => $this->readMinutes($locale),
        ];
    }

    /** المقال كامل لصفحة العرض */
    public function toArticle(string $locale): array
    {
        return $this->toCard($locale) + [
            'body' => $this->t('body', $locale) ?? '',
        ];
    }
}
