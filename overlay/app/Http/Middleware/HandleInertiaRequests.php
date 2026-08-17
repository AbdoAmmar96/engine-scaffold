<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Modules\Core\Services\SettingsService;

class HandleInertiaRequests extends Middleware
{
    /** الـ Blade root view */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props مشتركة بتوصل لكل صفحة React —
     * الإعدادات العامة (is_public فقط) + اللغة + المستخدم + رسائل الفلاش.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'settings' => fn () => app(SettingsService::class)->public(),
            'locale'   => fn () => app()->getLocale(),

            'auth' => [
                'user' => $request->user()?->only('id', 'name', 'email'),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
