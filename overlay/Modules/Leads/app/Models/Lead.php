<?php

namespace Modules\Leads\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Compounds\Models\Compound;
use Modules\Properties\Models\Property;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $owner_id
 * @property int|null $property_id
 * @property int|null $compound_id
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property string|null $area
 * @property string|null $budget
 * @property string|null $message
 * @property string $source
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property-read User|null $owner
 * @property-read User|null $user
 * @property-read Property|null $property
 * @property-read Compound|null $compound
 */
class Lead extends Model
{
    protected $fillable = [
        'user_id', 'owner_id', 'property_id', 'compound_id',
        'name', 'phone', 'email', 'area', 'budget', 'message', 'source', 'status', 'notes',
    ];

    /** مراحل المتابعة ولون الشارة في اللوحة */
    public const STATUSES = [
        'new' => ['label' => 'جديد', 'tone' => 'primary'],
        'contacted' => ['label' => 'تم التواصل', 'tone' => 'warn'],
        'qualified' => ['label' => 'مؤهّل', 'tone' => 'success'],
        'won' => ['label' => 'تمت الصفقة', 'tone' => 'success'],
        'lost' => ['label' => 'مغلق', 'tone' => 'muted'],
    ];

    public const SOURCES = [
        'contact' => 'فورم اتصل بنا',
        'hero' => 'بحث الصفحة الرئيسية',
        'property' => 'صفحة وحدة',
        'compound' => 'صفحة مشروع',
        'whatsapp' => 'واتساب',
        'phone' => 'مكالمة',
        'listing' => 'أضف عقارك',
        'manual' => 'إضافة يدوية',
    ];

    /** الوسيط/الشركة اللي الطلب من حقه */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** العميل المسجّل اللي بعت الطلب */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function compound(): BelongsTo
    {
        return $this->belongsTo(Compound::class);
    }

    /** الوحدة أو المشروع اللي الطلب عليه، للعرض */
    public function subject(?string $locale = null): ?string
    {
        return $this->property?->t('title', $locale) ?? $this->compound?->t('name', $locale);
    }
}
