<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Activity;

/**
 * بيسجّل الإضافة والتعديل والحذف في سجل النشاط.
 *
 * بيتركّب على الموديلات اللي فيها قرارات (عقارات، مستخدمين، إعدادات) —
 * مش على كل حاجة. العدّادات اللي بتتزوّد بالـ query builder (المشاهدات،
 * الظهور، الضغطات) مبتعديش من هنا أصلًا فالسجل مبيمتلاش ضوضاء.
 */
trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(fn (Model $model) => Activity::record('created', $model));
        static::updated(fn (Model $model) => Activity::record('updated', $model));
        static::deleted(fn (Model $model) => Activity::record('deleted', $model));
    }
}
