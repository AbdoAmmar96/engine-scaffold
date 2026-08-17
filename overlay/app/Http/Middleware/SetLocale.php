<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale', 'ar');

        abort_unless(in_array($locale, ['ar', 'en']), 404);

        app()->setLocale($locale);

        // عشان route() تحقن الـ locale تلقائيًا في كل الروابط المولّدة
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
