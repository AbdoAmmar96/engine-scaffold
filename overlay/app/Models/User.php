<?php

namespace App\Models;

use App\Support\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Modules\Compounds\Models\Compound;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Leads\Models\Lead;
use Modules\Properties\Models\Property;
use Modules\Properties\Models\SavedSearch;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $company_name
 * @property bool $is_active
 * @property Carbon|null $created_at
 */
class User extends Authenticatable
{
    use HasFactory, HasRoles, LogsActivity, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_name',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * الديفولت لازم يبقى على الموديل مش على الجدول بس: الصف الجديد
     * اللي اتعمل بـ create() من غير الحقل ده كان بيرجّع null، وأي فحص
     * `! $user->is_active` كان بيعتبره موقوف ويطرده من اللوحة.
     */
    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /** الوحدات اللي الحساب ده صاحبها */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    /** المشاريع اللي الحساب ده صاحبها */
    public function compounds(): HasMany
    {
        return $this->hasMany(Compound::class, 'owner_id');
    }

    /** الطلبات اللي من حق الحساب ده (وسيط/شركة) */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'owner_id');
    }

    /** الطلبات اللي الحساب ده بعتها كعميل */
    public function requests(): HasMany
    {
        return $this->hasMany(Lead::class, 'user_id');
    }

    /**
     * عمليات البحث المحفوظة + تنبيهاتها
     *
     * @return HasMany<SavedSearch, $this>
     */
    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    /** العقارات المحفوظة */
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'favorites')->withTimestamps();
    }

    /**
     * بيفتح لوحة التحكم؟
     *
     * الدور هو اللي بيحدد، مش «عنده صلاحية»: المعلن معاه «manage listings»
     * عشان يدير وحداته من «حسابي»، ولو استنتجناها من الصلاحيات كان
     * هيتحسب موظف وياخد لوحة مالهاش لازمة بالنسبة له.
     */
    public function isStaff(): bool
    {
        return $this->hasAnyRole(RolePermissionSeeder::staffRoles());
    }

    /** بيملك وحدات ويديرها بنفسه — وسيط أو شركة أو معلن */
    public function ownsListings(): bool
    {
        return $this->can('manage listings');
    }

    /**
     * بيشوف كل الصفوف ولا بتاعه بس؟
     * «manage catalog» هي خط الفصل — الأدمن معاه والوسيط لأ.
     */
    public function seesEverything(): bool
    {
        return $this->can('manage catalog');
    }

    /** اسم العرض — الشركة بتظهر باسمها التجاري */
    public function displayName(): string
    {
        return filled($this->company_name) ? $this->company_name : $this->name;
    }
}
