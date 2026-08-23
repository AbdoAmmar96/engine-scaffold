<?php

namespace Modules\Properties\Models;

use App\Models\User;
use App\Support\Catalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * بحث محفوظ للعميل، مع تنبيه اختياري لما ينزل اللي يطابقه.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property array $filters
 * @property bool $alerts
 * @property int $last_property_id
 * @property Carbon|null $last_alert_at
 * @property Carbon|null $created_at
 * @property-read User $user
 */
class SavedSearch extends Model
{
    protected $fillable = ['user_id', 'name', 'filters', 'alerts', 'last_property_id', 'last_alert_at'];

    protected $casts = [
        'filters' => 'array',
        'alerts' => 'boolean',
        'last_property_id' => 'integer',
        'last_alert_at' => 'datetime',
    ];

    protected $attributes = ['alerts' => true, 'last_property_id' => 0];

    /** أقصى عدد بحوث محفوظة للحساب الواحد */
    public const LIMIT = 20;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** الرابط اللي بيرجّع الزائر لنفس النتيجة */
    public function url(string $locale): string
    {
        $query = array_filter($this->filters, fn ($v) => $v !== '' && $v !== null);

        return "/{$locale}/properties".($query ? '?'.http_build_query($query) : '');
    }

    /**
     * الوحدات الجديدة المطابقة — «جديدة» يعني id أكبر من آخر واحدة
     * اتبعت، مش «اتنشرت النهارده»: الوحدة اللي رجعت للمراجعة واتنشرت
     * تاني مش جديدة على اللي شافها قبل كده.
     *
     * @return Collection<int, Property>
     */
    public function newMatches(int $limit = 6)
    {
        return Property::published()
            ->where('id', '>', $this->last_property_id)
            ->with(['location', 'compound.location'])
            ->tap(fn ($q) => Catalog::applyFilters($q, $this->filters))
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /** ملخّص الفلاتر للعرض تحت اسم البحث */
    public function summary(string $locale): array
    {
        return collect($this->filters)
            ->filter(fn ($v) => $v !== '' && $v !== null)
            ->map(fn ($v, $k) => Catalog::filterLabel($k, (string) $v, $locale))
            ->filter()
            ->values()
            ->all();
    }
}
