<?php

namespace Modules\Leads\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
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
        'whatsapp' => 'واتساب',
        'phone' => 'مكالمة',
        'manual' => 'إضافة يدوية',
    ];
}
