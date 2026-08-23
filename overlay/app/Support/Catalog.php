<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Blog\Models\Post;
use Modules\Compounds\Models\Compound;
use Modules\Developers\Models\Developer;
use Modules\Locations\Models\Location;
use Modules\Properties\Models\Property;
use Modules\Reviews\Models\Review;

/**
 * مصدر بيانات الموقع العام — بيقرا من الجداول الحقيقية.
 * لو الجداول لسه فاضية (تثبيت جديد قبل الـ seed) بيرجع لـ DemoContent
 * عشان الموقع ميطلعش فاضي.
 */
class Catalog
{
    /**
     * الفلاتر المسموحة ونوع كل واحدة — الحارس الوحيد لأي قيمة جاية من الرابط.
     * أي مفتاح مش هنا بيتجاهل، فمحدش يقدر يحقن عمود أو ترتيب من الكويري سترنج.
     */
    private const FILTER_SCHEMA = [
        'q' => 'text',
        'type' => 'text',
        'location' => 'text',
        'developer' => 'text',
        'compound' => 'text',
        'purpose' => 'purpose',
        'category' => 'category',
        'finishing' => 'finishing',
        'sort' => 'sort',
        'price_min' => 'int',
        'price_max' => 'int',
        'area_min' => 'int',
        'area_max' => 'int',
        'beds' => 'int',
        'baths' => 'int',
        'down_max' => 'int',
        'monthly_max' => 'int',
        'years_max' => 'int',
        'delivery' => 'int',
        'featured' => 'bool',
        'garden' => 'bool',
        'roof' => 'bool',
        'dressing' => 'bool',
    ];

    /** خيارات الترتيب — المفتاح بيتحط في الرابط */
    public const SORTS = [
        'newest' => ['ar' => 'الأحدث', 'en' => 'Newest'],
        'oldest' => ['ar' => 'الأقدم', 'en' => 'Oldest'],
        'price_asc' => ['ar' => 'السعر: من الأقل', 'en' => 'Price: low to high'],
        'price_desc' => ['ar' => 'السعر: من الأعلى', 'en' => 'Price: high to low'],
        'area_desc' => ['ar' => 'المساحة: الأكبر', 'en' => 'Area: largest'],
        'area_asc' => ['ar' => 'المساحة: الأصغر', 'en' => 'Area: smallest'],
    ];

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

        $rows = Property::published()
            ->with(['location', 'developer', 'compound.developer', 'compound.location'])
            ->tap(fn ($q) => self::applyPropertyFilters($q, $filters))
            ->tap(fn ($q) => self::applyPropertySort($q, (string) ($filters['sort'] ?? '')))
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

        // القسم بيتحدد من قايمة الأنواع مش من عمود، فمصدر الحقيقة واحد
        if (in_array($filters['category'] ?? null, array_keys(Property::CATEGORIES), true)) {
            $query->whereIn('type', Property::typesIn($filters['category']));
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

        // زي المطوّر: الوحدة جوه مشروع بتورّث منطقته لو مش متكتبة عليها،
        // والكارت بيعرض المنطقة الموروثة — فالفلتر لازم يلاقيها برضه
        if ($location = trim((string) ($filters['location'] ?? ''))) {
            $match = fn ($s) => $s->where('name', $location)
                ->orWhere('name_en', $location)
                ->orWhere('slug', $location);

            $query->where(fn ($s) => $s
                ->whereHas('location', $match)
                ->orWhere(fn ($inner) => $inner
                    ->whereNull('location_id')
                    ->whereHas('compound.location', $match)));
        }

        // المطوّر ممكن يكون على الوحدة نفسها أو على مشروعها
        if ($developer = trim((string) ($filters['developer'] ?? ''))) {
            $match = fn ($s) => $s->where('name', $developer)
                ->orWhere('name_en', $developer)
                ->orWhere('slug', $developer);

            $query->where(fn ($s) => $s
                ->whereHas('developer', $match)
                ->orWhereHas('compound.developer', $match));
        }

        if ($compound = trim((string) ($filters['compound'] ?? ''))) {
            $query->whereHas('compound', fn ($s) => $s
                ->where('name', $compound)
                ->orWhere('name_en', $compound)
                ->orWhere('slug', $compound));
        }

        if (isset(Property::FINISHING[$filters['finishing'] ?? ''])) {
            $query->where('finishing', $filters['finishing']);
        }

        // النطاقات: الوحدة اللي مالهاش رقم مبتظهرش في فلتر رقمي —
        // أحسن من إنها تظهر في كل نطاق وتضلّل الباحث
        self::range($query, 'price_amount', $filters['price_min'] ?? null, $filters['price_max'] ?? null);
        self::range($query, 'size', $filters['area_min'] ?? null, $filters['area_max'] ?? null);

        foreach (['beds', 'baths'] as $column) {
            if ($min = (int) ($filters[$column] ?? 0)) {
                // «٣ غرف» معناها ٣ أو أكتر — زي كل بوابات العقارات
                $query->where($column, '>=', $min);
            }
        }

        $atMost = [
            'down_max' => 'down_payment',
            'monthly_max' => 'monthly_installment',
            'years_max' => 'installment_years',
            'delivery' => 'delivery_year',
        ];

        foreach ($atMost as $key => $column) {
            if ($max = (int) ($filters[$key] ?? 0)) {
                $query->whereNotNull($column)->where($column, '<=', $max);
            }
        }

        $flags = [
            'featured' => 'is_featured',
            'garden' => 'has_garden',
            'roof' => 'has_roof',
            'dressing' => 'has_dressing_room',
        ];

        foreach ($flags as $key => $column) {
            if (! empty($filters[$key])) {
                $query->where($column, true);
            }
        }
    }

    /**
     * تطبيق الفلاتر على استعلام جاهز — نفس منطق الصفحة بالظبط.
     * التنبيهات بتستخدمه عشان اللي في الإيميل يبقى هو اللي في الموقع.
     */
    /**
     * آراء العملاء المعتمدة.
     *
     * مفيش fallback لـ DemoContent هنا عن قصد: باقي الكتالوج بيرجع لبيانات
     * تجريبية على التثبيت الجديد عشان الصفحة تبان، بس رأي عميل متلفّق حاجة
     * تانية خالص. القسم بيختفي وهو فاضي.
     *
     * @return list<array<string, mixed>>
     */
    public static function reviews(string $locale, int $limit = 6): array
    {
        if (! Schema::hasTable('reviews')) {
            return [];
        }

        return Review::published()
            ->orderBy('sort')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (Review $r) => $r->toCard($locale))
            ->all();
    }

    public static function applyFilters(Builder $query, array $filters): void
    {
        self::applyPropertyFilters($query, $filters);
    }

    /** اسم الفلتر وقيمته في جملة — للإيميل ولملخّص البحث المحفوظ */
    public static function filterLabel(string $key, string $value, string $locale): ?string
    {
        if ($value === '' || ! isset(self::FILTER_SCHEMA[$key])) {
            return null;
        }

        $en = $locale === 'en';

        $names = $en
            ? ['q' => 'Search', 'type' => 'Type', 'location' => 'Area', 'purpose' => 'Purpose',
                'category' => 'Section', 'finishing' => 'Finishing', 'developer' => 'Developer',
                'compound' => 'Project', 'sort' => 'Sort', 'price_min' => 'Price from',
                'price_max' => 'Price to', 'area_min' => 'Size from', 'area_max' => 'Size to',
                'beds' => 'Beds', 'baths' => 'Baths', 'down_max' => 'Max down',
                'monthly_max' => 'Max instalment', 'years_max' => 'Max years',
                'delivery' => 'Delivered before', 'featured' => 'Featured', 'garden' => 'Garden',
                'roof' => 'Roof', 'dressing' => 'Dressing room']
            : ['q' => 'بحث', 'type' => 'النوع', 'location' => 'المنطقة', 'purpose' => 'الغرض',
                'category' => 'القسم', 'finishing' => 'التشطيب', 'developer' => 'المطوّر',
                'compound' => 'المشروع', 'sort' => 'الترتيب', 'price_min' => 'سعر من',
                'price_max' => 'سعر إلى', 'area_min' => 'مساحة من', 'area_max' => 'مساحة إلى',
                'beds' => 'غرف', 'baths' => 'حمامات', 'down_max' => 'أقصى مقدم',
                'monthly_max' => 'أقصى قسط', 'years_max' => 'أقصى سنوات',
                'delivery' => 'تسليم قبل', 'featured' => 'مميّزة', 'garden' => 'حديقة',
                'roof' => 'روف', 'dressing' => 'غرفة ملابس'];

        $shown = match (self::FILTER_SCHEMA[$key]) {
            'bool' => $en ? 'yes' : 'أيوه',
            'purpose' => $value === 'rent' ? ($en ? 'Rent' : 'إيجار') : ($en ? 'Sale' : 'بيع'),
            'category' => Property::CATEGORIES[$value][$en ? 'en' : 'ar'] ?? $value,
            'finishing' => Property::FINISHING[$value][$en ? 'en' : 'ar'] ?? $value,
            'sort' => self::SORTS[$value][$en ? 'en' : 'ar'] ?? $value,
            'int' => number_format((int) $value),
            default => $value,
        };

        return $names[$key].': '.$shown;
    }

    /**
     * عدد الوحدات المطابقة لفلاتر — بنفس منطق صفحة النتايج بالظبط.
     * صفحات الهبوط بتعتمد عليها: العدد اللي الأمر بيخزّنه لازم يكون هو
     * اللي الزائر هيشوفه، وإلا الصفحة بتوعد بوحدات مش موجودة.
     */
    public static function countProperties(array $filters): int
    {
        return Property::published()
            ->tap(fn ($q) => self::applyPropertyFilters($q, $filters))
            ->count();
    }

    /** نطاق رقمي — بيتجاهل الوحدات اللي العمود ده فاضي عندها */
    private static function range($query, string $column, mixed $min, mixed $max): void
    {
        if ($from = (int) $min) {
            $query->whereNotNull($column)->where($column, '>=', $from);
        }

        if ($to = (int) $max) {
            $query->whereNotNull($column)->where($column, '<=', $to);
        }
    }

    /**
     * المميّز بيتصدّر الترتيب الافتراضي بس. لما المستخدم يختار ترتيب صريح
     * («السعر من الأقل») بيتنفّذ زي ما طلبه — وإلا الترتيب بيبان مكسور.
     */
    private static function applyPropertySort($query, string $sort): void
    {
        if ($sort === '' || $sort === 'newest') {
            $query->orderByDesc('is_featured');
        }

        match ($sort) {
            'oldest' => $query->orderBy('id'),
            // الوحدات بلا سعر تحت في الترتيب التصاعدي بدل ما تتصدّر النتايج
            'price_asc' => $query->orderByRaw('price_amount is null')->orderBy('price_amount'),
            'price_desc' => $query->orderByDesc('price_amount'),
            'area_asc' => $query->orderByRaw('size = 0')->orderBy('size'),
            'area_desc' => $query->orderByDesc('size'),
            default => $query->orderBy('sort')->orderByDesc('id'),
        };
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
            [Property::published()->count(), $ar ? 'عقار' : 'properties'],
            [Compound::where('is_active', true)->count(), $ar ? 'كمبوند' : 'compounds'],
            [Developer::count(), $ar ? 'مطوّر' : 'developers'],
        ];

        return collect($counts)
            ->map(fn ($c) => ['value' => (string) $c[0], 'suffix' => '', 'label' => $c[1]])
            ->all();
    }

    /** الفلاتر المسموحة من الـ query string، منضّفة حسب السكيما */
    public static function filters(Request $request): array
    {
        $out = [];

        foreach (self::FILTER_SCHEMA as $key => $kind) {
            $raw = $request->query($key);

            $out[$key] = match ($kind) {
                'int' => ($n = (int) $raw) > 0 ? $n : '',
                'bool' => in_array($raw, ['1', 'true', 'on'], true) ? '1' : '',
                'purpose' => in_array($raw, ['sale', 'rent'], true) ? $raw : '',
                'category' => in_array($raw, array_keys(Property::CATEGORIES), true) ? $raw : '',
                'finishing' => isset(Property::FINISHING[$raw]) ? $raw : '',
                'sort' => isset(self::SORTS[$raw]) ? $raw : '',
                default => Str::limit(trim((string) $raw), 80, ''),
            };
        }

        return $out;
    }

    /** بطاقات المناطق في الرئيسية — بعدد العقارات الحقيقي */
    public static function areas(string $locale, ?int $limit = 3): array
    {
        $rows = Location::query()
            ->where('is_active', true)
            ->withCount(['properties' => fn ($q) => $q->published()])
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

    /**
     * كل المطوّرين لصفحة /developers — من غير فلترة على وجود مشاريع،
     * عكس developers() اللي بتغذّي قسم الرئيسية.
     */
    public static function allDevelopers(string $locale): array
    {
        $rows = Developer::query()
            ->where('is_active', true)
            ->withCount([
                'compounds' => fn ($q) => $q->where('is_active', true),
                'properties' => fn ($q) => $q->published(),
            ])
            ->orderByDesc('compounds_count')
            ->orderBy('sort')->orderBy('id')
            ->get();

        return $rows->map(fn (Developer $d) => $d->toCard($locale) + [
            'units' => (int) $d->properties_count,
        ])->all();
    }

    /** صفحة مطوّر — بياناته + مشاريعه + وحداته + المناطق اللي بيشتغل فيها */
    public static function developer(string $locale, string $slug): ?array
    {
        $developer = Developer::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->withCount([
                'compounds' => fn ($q) => $q->where('is_active', true),
                'properties' => fn ($q) => $q->published(),
            ])
            ->first();

        if (! $developer) {
            return null;
        }

        $compounds = Compound::query()
            ->where('is_active', true)
            ->where('developer_id', $developer->id)
            ->with(['developer', 'location'])
            ->orderBy('sort')->orderByDesc('id')
            ->get();

        // وحدات المطوّر: المربوطة بيه مباشرة + اللي جوه مشاريعه
        $units = Property::published()
            ->where(fn ($q) => $q
                ->where('developer_id', $developer->id)
                ->orWhereIn('compound_id', $compounds->pluck('id')))
            ->with('location')
            ->orderBy('sort')->orderByDesc('id')
            ->limit(6)
            ->get();

        return [
            'developer' => $developer->toDetail($locale) + [
                'units' => $units->count(),
                'areas' => $compounds->pluck('location_id')->filter()->unique()->count(),
            ],
            'compounds' => $compounds->map(fn (Compound $c) => $c->toCard($locale))->all(),
            'units' => $units->map(fn (Property $p) => $p->toCard($locale))->all(),
        ];
    }

    /** صفحة منطقة — نبذتها + مشاريعها + وحداتها */
    public static function area(string $locale, string $slug): ?array
    {
        $location = Location::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->withCount([
                'properties' => fn ($q) => $q->published(),
                'compounds' => fn ($q) => $q->where('is_active', true),
            ])
            ->first();

        if (! $location) {
            return null;
        }

        $compounds = Compound::query()
            ->where('is_active', true)
            ->where('location_id', $location->id)
            ->with(['developer', 'location'])
            ->orderBy('sort')->orderByDesc('id')
            ->get();

        $properties = Property::published()
            ->where('location_id', $location->id)
            ->with('location')
            ->orderBy('sort')->orderByDesc('id')
            ->limit(6)
            ->get();

        return [
            'area' => $location->toDetail($locale) + [
                'properties' => (int) $location->properties_count,
                'compounds' => (int) $location->compounds_count,
                'developers' => $compounds->pluck('developer_id')->filter()->unique()->count(),
            ],
            'compounds' => $compounds->map(fn (Compound $c) => $c->toCard($locale))->all(),
            'properties' => $properties->map(fn (Property $p) => $p->toCard($locale))->all(),
        ];
    }

    /** كل المناطق المفعّلة للصفحة المخصّصة لها */
    public static function allAreas(string $locale): array
    {
        $rows = Location::query()
            ->where('is_active', true)
            ->withCount([
                'properties' => fn ($q) => $q->published(),
                'compounds' => fn ($q) => $q->where('is_active', true),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('sort')->orderBy('id')
            ->get();

        $ar = $locale !== 'en';

        return $rows->map(fn (Location $l) => $l->toCard($locale) + [
            'count' => $l->properties_count.' '.($ar ? 'وحدة' : 'units'),
            'compounds' => (int) $l->compounds_count,
            'properties' => (int) $l->properties_count,
        ])->all();
    }

    /**
     * «شوهدت مؤخرًا» للحساب المسجّل — بترجع فاضية للزائر،
     * وساعتها المتصفح بيجيبها من localStorage عبر /recently-viewed.
     */
    public static function recentlyViewed(string $locale, int $limit = 8): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $ids = DB::table('recently_viewed')
            ->where('user_id', $user->id)
            ->orderByDesc('viewed_at')
            ->limit($limit)
            ->pluck('property_id');

        if ($ids->isEmpty()) {
            return [];
        }

        $rows = Property::published()
            ->whereIn('id', $ids)
            ->with(['location', 'compound.developer', 'compound.location', 'developer'])
            ->get()
            ->sortBy(fn (Property $p) => $ids->search($p->id));

        return $rows->map(fn (Property $p) => $p->toCard($locale))->values()->all();
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

        return Property::published()
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

        $rows = Property::published()
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
        $rows = Property::published()
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

        // خيارات لوحة الفلاتر المتقدمة
        $en = $locale === 'en';

        $base['finishing'] = collect(Property::FINISHING)
            ->map(fn ($labels, $key) => ['value' => $key, 'label' => $labels[$en ? 'en' : 'ar']])
            ->values()->all();

        $base['sorts'] = collect(self::SORTS)
            ->map(fn ($labels, $key) => ['value' => $key, 'label' => $labels[$en ? 'en' : 'ar']])
            ->values()->all();

        $base['developers'] = Developer::where('is_active', true)
            ->orderBy('sort')->orderBy('id')->get()
            ->map(fn (Developer $d) => ['value' => $d->name, 'label' => $d->t('name', $locale)])
            ->all();

        $base['compounds'] = Compound::where('is_active', true)
            ->orderBy('sort')->orderBy('id')->get()
            ->map(fn (Compound $c) => ['value' => $c->name, 'label' => $c->t('name', $locale)])
            ->all();

        // حدود المنزلقات — من المعروض فعلًا مش أرقام مكتوبة بالإيد
        $bounds = Property::published()->selectRaw(
            'min(price_amount) as price_min, max(price_amount) as price_max, min(nullif(size, 0)) as area_min, max(size) as area_max'
        )->first();

        $base['bounds'] = [
            'priceMin' => (int) ($bounds->price_min ?? 0),
            'priceMax' => (int) ($bounds->price_max ?? 0),
            'areaMin' => (int) ($bounds->area_min ?? 0),
            'areaMax' => (int) ($bounds->area_max ?? 0),
        ];

        return $base;
    }
}
