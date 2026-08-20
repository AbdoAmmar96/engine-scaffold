<?php

namespace Modules\Core\Http\Controllers;

use App\Models\User;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * تسجيل دخول واشتراك العملاء من الموقع نفسه (مش لوحة التحكم).
 * نفس الجارد بتاع اللوحة — الفرق إن العميل مالوش صلاحيات، فـ EnsureStaff
 * بيرجّعه لمساحته لو حاول يفتح /admin.
 */
class AccountAuthController extends Controller
{
    public function showLogin(string $locale): Response
    {
        return Inertia::render('Site/Auth/Login', [
            'meta' => Seo::page($locale, $locale === 'en' ? 'Sign in' : 'تسجيل الدخول'),
        ]);
    }

    public function showRegister(string $locale): Response
    {
        return Inertia::render('Site/Auth/Register', [
            'meta' => Seo::page($locale, $locale === 'en' ? 'Create an account' : 'حساب جديد'),
        ]);
    }

    public function login(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($data, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => $locale === 'en' ? 'Invalid credentials.' : 'بيانات الدخول غير صحيحة.',
            ]);
        }

        if (! $request->user()->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => $locale === 'en' ? 'This account is suspended.' : 'الحساب ده موقوف — كلّم إدارة المنصّة.',
            ]);
        }

        $request->session()->regenerate();

        return $this->afterLogin($request, $locale);
    }

    public function register(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'name' => 'الاسم',
            'email' => 'الإيميل',
            'phone' => 'الموبايل',
            'password' => 'كلمة المرور',
        ]);

        // التسجيل العام بيطلّع عملاء بس — أي دور تاني بيتعمل من اللوحة
        $user = User::create($data);
        $user->syncRoles(['customer']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('account.index', ['locale' => $locale])
            ->with('success', $locale === 'en' ? 'Welcome 👋' : 'أهلًا بيك 👋');
    }

    public function logout(Request $request, string $locale): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home', ['locale' => $locale]);
    }

    /** الموظف بيروح اللوحة والعميل بيروح مساحته */
    private function afterLogin(Request $request, string $locale): RedirectResponse
    {
        if ($request->user()->isStaff()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('account.index', ['locale' => $locale]));
    }
}
