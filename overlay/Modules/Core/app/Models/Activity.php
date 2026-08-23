<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * صف واحد في سجل النشاط.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $user_label
 * @property string $action
 * @property string $subject_type
 * @property int|null $subject_id
 * @property string|null $subject_label
 * @property array|null $changed
 * @property string|null $ip
 * @property Carbon|null $created_at
 * @property-read User|null $user
 */
class Activity extends Model
{
    protected $table = 'activity_log';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'user_label', 'action', 'subject_type', 'subject_id',
        'subject_label', 'changed', 'ip', 'created_at',
    ];

    protected $casts = ['changed' => 'array', 'created_at' => 'datetime'];

    public const ACTIONS = [
        'created' => ['label' => 'إضافة', 'tone' => 'success'],
        'updated' => ['label' => 'تعديل', 'tone' => 'primary'],
        'deleted' => ['label' => 'حذف', 'tone' => 'danger'],
    ];

    /** أسماء عربية للكيانات — أي كلاس مش هنا بيظهر باسم الكلاس */
    public const SUBJECTS = [
        'Property' => 'عقار',
        'Compound' => 'كمبوند',
        'Developer' => 'مطوّر',
        'Location' => 'منطقة',
        'Lead' => 'طلب',
        'Post' => 'مقال',
        'User' => 'مستخدم',
        'MenuItem' => 'عنصر قائمة',
        'Setting' => 'إعداد',
        'FeaturedAd' => 'مساحة إعلانية',
        'LandingPage' => 'صفحة هبوط',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjectLabel(): string
    {
        return self::SUBJECTS[class_basename($this->subject_type)] ?? class_basename($this->subject_type);
    }

    /**
     * تسجيل فعل.
     *
     * بيتجاهل اللي مش صادر عن إنسان مسجّل دخوله (سيدر، أمر، جدولة)،
     * واللي مغيّرش حاجة فعلًا.
     */
    public static function record(string $action, Model $subject): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $changed = $action === 'updated'
            ? array_values(array_diff(array_keys($subject->getChanges()), ['updated_at', ...$subject->getHidden()]))
            : null;

        if ($action === 'updated' && $changed === []) {
            return;
        }

        self::create([
            'user_id' => $user->id,
            // الاسم متخزّن كنص عشان السطر يفضل مقروء لو الحساب اتمسح
            'user_label' => $user->displayName(),
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'subject_label' => Str::limit((string) self::describe($subject), 120, ''),
            'changed' => $changed,
            'ip' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    /** وصف الصف — أول حقل معروف فيه اسم */
    private static function describe(Model $subject): string
    {
        foreach (['title', 'name', 'label', 'slug', 'email', 'key'] as $field) {
            if (filled($value = $subject->getAttribute($field))) {
                return (string) $value;
            }
        }

        return '#'.$subject->getKey();
    }
}
