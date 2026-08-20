<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * لوحة التحكم للموظفين بس. العميل بيسجّل من الموقع وبيدخل على نفس الجارد،
 * فمن غير الحاجز ده كان هيقدر يفتح /admin ويتفرّج على شاشة فاضية و403ات.
 * بنوديه مساحته بدل ما نطلّعه رسالة خطأ.
 */
class EnsureStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isStaff()) {
            return redirect()->route('account.index', ['locale' => app()->getLocale()]);
        }

        // الحساب الموقوف بيتسجّل خروجه فورًا
        if ($user && ! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('error', 'الحساب ده موقوف — كلّم إدارة المنصّة.');
        }

        return $next($request);
    }
}
