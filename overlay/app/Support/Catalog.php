<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Blog\Models\Post;
use Modules\Compounds\Models\Compound;
use Modules\Developers\Models\Developer;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;

/**
 * مصدر بيانات الموقع العام — بيقرا من الجداول الحقيقية.
 * لو الجداول لسه فاضية (تثبيت جديد قبل الـ seed) بيرجع لـ DemoContent
 * عشان الموقع ميطلعش فاضي.
 */
class Catalog
{
    /**
     * @param  array  $filters  q · type · location · purpose — جايين من فورم البحث في الهيرو
     */
    public static function properties(string $locale, ?int $limit = null, array $filters = []): array
    {
        // الفولباك بيتحدد بجدول فاضي مش بنتيجة فاضية،
        // عشان بحث ملقاش حاجة ميرجّعش بيانات تجريبية بدل "مفيش نتايج"
        if (! Property::query()->exists()) {
            $demo = self::withDemoSlugs(DemoContent::properties($locale), 'property');

            return $limit ? array_slice($demo, 0, $limit) : $demo;
        }

        $rows = Property::query()
            ->where('is_active', true)
            ->with(['location', 'developer', 'compound.developer', 'compound.location'])
            ->tap(fn ($q) => self::applyPropertyFilters($q, $filters))
            ->orderBy('sort')->orderByDesc('id')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get();

        return $rows->map(fn (Property $p) => $p->toCard($locale))->all();
    }

    private static function applyPropertyFilters($query, array $filters): void
    {
        if ($q = trim((string) ($filters['q'] ?? ''))) {
            // الـ placeholder في الهيرو بيوعد بالمنطقة والكمبوند والمطوّر — فلازم
            // البحث يغطيهم كلهم، مش العنوان والكود بس.
            // المطوّر بيتدوّر عليه في العمود المباشر وفي مطوّر الكمبوند سوا،
            // عشان الوحدة المستقلة والوحدة اللي جوه مشروع يطلعوا في نفس النتيجة.
            $like = fn ($s) => $s->where('name', 'like', "%{$q}%")->orWhere('name_en', 'like', "%{$q}%");

            $query->where(fn ($s) => $s
                ->where('title', 'like', "%{$q}%")
                ->orWhere('title_en', 'like', "%{$q}%")
                ->orWhere('ref', 'like', "%{$q}%")
                ->orWhereHas('location', $like)
                ->orWhereHas('compound', $like)
                ->orWhereHas('developer', $like)
                // الوحدة جوه مشروع بتورّث منطقته ومطوّره لو مش متكتبين عليها
                ->orWhereHas('compound.location', $like)
                ->orWhereHas('compound.developer', $like));
        }

        if ($type = trim((string) ($filters['type'] ?? ''))) {
            // القيمة متخزّنة بالعربي، فالبحث الإنجليزي (Villa) بيترجم الأول
            $type = Property::TYPES[$type] ?? false
                ? $type
                : (array_flip(Property::TYPES)[$type] ?? $type);

            $query->where('type', $type);
        }

        if (in_array($filters['purpose'] ?? null, ['sale', 'rent'], true)) {
            $query->where('purpose', $filters['purpose']);
        }

        if ($location = trim((string) ($filters['location'] ?? ''))) {
            $query->whereHas('location', fn ($s) => $s
                ->where('name', $location)
                ->orWhere('name_en', $location));
        }
    }

    /**
     * @param  array  $filters  q · location — نفس فورم الهيرو في تبويب "مشروع"
     */
    public static function compounds(string $locale, ?int $limit = null, array $filters = []): array
    {
        if (! Compound::query()->exists()) {
            $demo = self::withDemoSlugs(DemoContent::compounds($locale), 'compound');

            return $limit ? array_slice($demo, 0, $limit) : $demo;
        }

        $rows = Compound::query()
            ->where('is_active', true)
            ->with(['developer', 'location'])
            ->when(trim((string) ($filters['q'] ?? '')), fn ($query, $q) => $query
                ->where(fn ($s) => $s
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('name_en', 'like', "%{$q}%")
                    ->orWhereHas('developer', fn ($d) => $d->where('name', 'like', "%{$q}%"))))
            ->when(trim((string) ($filters['location'] ?? '')), fn ($query, $loc) => $query
                ->whereHas('location', fn ($s) => $s->where('name', $loc)->orWhere('name_en', $loc)))
            ->orderBy('sort')->orderByDesc('id')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get();

        return $rows->map(fn (Compound $c) => $c->toCard($locale))->all();
    }

    /**
     * المطوّرون المعروضين في الموقع — بعدد مشاريعهم الحقيقي.
     *
     * المطوّر من غير مشاريع منشورة مش بيظهر: القسم ده بيوعد الزائر بمشاريع
     * يقدر يشوفها، وكارت بيوصّل لصفحة فاضية أسوأ من إنه مايبانش.
     */
    public static function developers(string $locale, ?int $limit = null): array
    {
        $rows = Developer::query()
            ->where('is_active', true)
            // whereHas مش having: withCount بيطلّع subquery مش aggregate،
            // و HAVING عليه بيرمي "non-aggregate query" في SQLite
            ->withCount(['compounds' => fn ($q) => $q->where('is_active', true)])
            ->whereHas('compounds', fn ($q) => $q->where('is_active', true))
            ->orderBy('sort')->orderBy('id')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get();

        return $rows->map(fn (Developer $d) => $d->toCard($locale))->all();
    }

    /** إحصاءات الهيرو — معدودة من الداتابيز مش مكتوبة بالإيد */
    public static function stats(string $locale): array
    {
        $ar = $locale !== 'en';

        $counts = [
            [Property::where('is_active', true)->count(), $ar ? 'عقار' : 'properties'],
            [Compound::where('is_active', true)->count(), $ar ? 'كمبوند' : 'compounds'],
            [Developer::count(), $ar ? 'مطوّر' : 'developers'],
        ];

        return collect($counts)
            ->map(fn ($c) => ['value' => (string) $c[0], 'suffix' => '', 'label' => $c[1]])
            ->all();
    }

    /** الفلاتر المسموحة من الـ query string، منضّفة */
    public static function filters(Request $request): array
    {
        return [
            'q' => Str::limit(trim((string) $request->query('q', '')), 80, ''),
            'type' => trim((string) $request->query('type', '')),
            'location' => trim((string) $request->query('location', '')),
            'purpose' => in_array($request->query('purpose'), ['sale', 'rent'], true) ? $request->query('purpose') : '',
        ];
    }

    /** بطاقات المناطق في الرئيسية — بعدد العقارات الحقيقي */
    public static function areas(string $locale, ?int $limit = 3): array
    {
        $rows = Location::query()
            ->where('is_active', true)
            ->withCount(['properties' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort')->orderBy('id')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get();

        if ($rows->isEmpty()) {
            $demo = DemoContent::areas($locale);

            return $limit ? array_slice($demo, 0, $limit) : $demo;
        }

        $ar = $locale !== 'en';

        return $rows->map(fn (Location $l) => $l->toCard($locale) + [
            'count' => $l->properties_count.' '.($ar ? 'وحدة' : 'units'),
        ])->all();
    }

    /** مقالات المدونة المنشورة */
    public static function posts(string $locale, ?int $limit = null): array
    {
        $rows = Post::published()
            ->orderBy('sort')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get();

        return $rows->map(fn (Post $p) => $p->toCard($locale))->all();
    }

    /** مقال واحد بالرابط — null لو مش موجود أو مش منشور */
    public static function post(string $locale, string $slug): ?array
    {
        return Post::published()->where('slug', $slug)->first()?->toArticle($locale);
    }

    /** عقار واحد بالرابط — null لو مش موجود أو متوقّف */
    public static function property(string $locale, string $slug): ?array
    {
        if (! Property::query()->exists()) {
            $demo = self::withDemoSlugs(DemoContent::properties($locale), 'property');
            $hit = collect($demo)->firstWhere('slug', $slug);

            return $hit ? $hit + ['description' => '', 'features' => [], 'gallery' => [$hit['image']], 'compound' => null] : null;
        }

        return Property::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->with(['location', 'developer', 'compound.developer', 'compound.location'])
            ->first()
            ?->toDetail($locale);
    }

    /** كمبوند واحد بالرابط — null لو مش موجود أو متوقّف */
    public static function compound(string $locale, string $slug): ?array
    {
        if (! Compound::query()->exists()) {
            $demo = self::withDemoSlugs(DemoContent::compounds($locale), 'compound');
            $hit = collect($demo)->firstWhere('slug', $slug);

            return $hit ? $hit + ['features' => [], 'gallery' => [$hit['image']]] : null;
        }

        return Compound::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->with(['developer', 'location'])
            ->first()
            ?->toDetail($locale);
    }

    /** عقارات شبه المعروض — نفس المنطقة أو نفس النوع */
    public static function relatedProperties(string $locale, array $property, int $limit = 3): array
    {
        if (! Property::query()->exists()) {
            return [];
        }

        $rows = Property::query()
            ->where('is_active', true)
            ->where('id', '!=', $property['id'])
            ->with(['location', 'developer', 'compound.developer', 'compound.location'])
            ->where(fn ($q) => $q
                ->whereHas('location', fn ($l) => $l->where('name', $property['area'])->orWhere('name_en', $property['area']))
                ->when($property['type'] ?? '', fn ($s, $type) => $s->orWhere('type', $type)))
            ->orderBy('sort')->orderByDesc('id')
            ->limit($limit)
            ->get();

        return $rows->map(fn (Property $p) => $p->toCard($locale))->all();
    }

    /** الوحدات المتاحة جوّه كمبوند */
    public static function compoundUnits(string $locale, int $compoundId, ?int $limit = null): array
    {
        $rows = Property::query()
            ->where('is_active', true)
            ->where('compound_id', $compoundId)
            ->with(['location', 'developer', 'compound.developer', 'compound.location'])
            ->orderBy('sort')->orderByDesc('id')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get();

        return $rows->map(fn (Property $p) => $p->toCard($locale))->all();
    }

    /**
     * بيانات DemoContent مالهاش slug — بنولّده هنا عشان كروت التثبيت الجديد
     * (قبل الـ seed) تفضل تفتح صفحة تفاصيل. ثابت مع اختلاف اللغة.
     */
    private static function withDemoSlugs(array $rows, string $kind): array
    {
        return array_map(function (array $row) use ($kind) {
            $row['slug'] = $kind === 'property'
                ? Str::slug((string) ($row['ref'] ?? ''))
                : $kind.'-'.$row['id'];

            $row['type'] ??= '';

            return $row;
        }, $rows);
    }

    /** خيارات البحث في الهيرو — الأنواع ثابتة والمناطق من الجدول */
    public static function searchOptions(string $locale): array
    {
        $base = DemoContent::searchOptions($locale);

        // الأنواع من نفس ثابت الموديل — لازم القيمة تطابق اللي متخزّن
        $base['types'] = $locale === 'en'
            ? array_values(Property::TYPES)
            : array_keys(Property::TYPES);

        // الأرقام دي كانت ثابتة في الكود (6000 عقار · 420 كمبوند · 161 مطوّر)
        // وهي ادعاءات مش صحيحة. بقت محسوبة من الجداول — بتكبر لوحدها
        // وبتفضل صح مهما اتغيّر المحتوى.
        $base['stats'] = self::stats($locale);

        $locations = Location::where('is_active', true)->orderBy('sort')->orderBy('id')->get();

        if ($locations->isNotEmpty()) {
            $base['locations'] = $locations->map(fn (Location $l) => $l->t('name', $locale))->all();
        }

        return $base;
    }
}
