<?php

namespace Modules\Core\Http\Controllers;

use App\Models\User;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * نسيت كلمة المرور.
 *
 * بيخدم اللوحة والموقع سوا — الحساب واحد والجارد واحد، فمفيش داعي
 * لمسارين. بعد ما يغيّرها بيتسجّل دخوله ويتوجّه لمكانه الصح.
 *
 * مبنقولش أبدًا «الإيميل ده مش موجود»: الرسالة واحدة في الحالتين عشان
 * الصفحة ماتبقاش أداة يعرف بيها حد مين عنده حساب هنا.
 */
class PasswordResetController extends Controller
{
    public function showRequest(string $locale): Response
    {
        return Inertia::render('Site/Auth/ForgotPassword', [
            'meta' => Seo::page($locale, $locale === 'en' ? 'Reset your password' : 'استعادة كلمة المرور'),
        ]);
    }

    public function sendLink(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate(
            ['email' => ['required', 'email', 'max:190']],
            [],
            ['email' => 'البريد الإلكتروني'],
        );

        $done = $locale === 'en'
            ? 'If that email has an account, a reset link is on its way ✅'
            : 'إن كان لهذا البريد حساب، فرابط التغيير في طريقه إليه ✅';

        // الحساب الموقوف مبياخدش لينك — تغيير الكلمة مش بيرجّعه
        $user = User::where('email', $data['email'])->first();

        if ($user && ! $user->is_active) {
            return back()->with('success', $done);
        }

        $status = Password::sendResetLink($data);

        // الاستثناء الوحيد: المحاولة المتكرّرة — الرسالة دي مفيدة ومش بتفشي حاجة
        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => $locale === 'en'
                    ? 'A link was just sent — check your inbox before asking for another.'
                    : 'أُرسل رابط منذ قليل — تفقّد بريدك قبل طلب رابط آخر.',
            ]);
        }

        return back()->with('success', $done);
    }

    public function showReset(Request $request, string $locale, string $token): Response
    {
        return Inertia::render('Site/Auth/ResetPassword', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
            'meta' => Seo::page($locale, $locale === 'en' ? 'Choose a new password' : 'كلمة مرور جديدة'),
        ]);
    }

    public function reset(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
        ]);

        $status = Password::reset($data, function (User $user, string $password) {
            // الكاست hashed في الموديل بيشفّرها
            $user->forceFill(['password' => $password, 'remember_token' => null])->save();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => $locale === 'en'
                    ? 'This link is invalid or has expired — ask for a new one.'
                    : 'هذا الرابط غير صالح أو انتهت صلاحيته — اطلب رابطًا جديدًا.',
            ]);
        }

        return redirect()
            ->route('account.login', ['locale' => $locale])
            ->with('success', $locale === 'en'
                ? 'Password changed ✅ — sign in with the new one.'
                : 'تم تغيير كلمة المرور ✅ — سجّل دخولك بها.');
    }
}
