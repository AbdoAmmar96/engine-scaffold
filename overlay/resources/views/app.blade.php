<!DOCTYPE html>
@php
    /** @var \Modules\Core\Services\SettingsService $settings */
    $settings     = app(\Modules\Core\Services\SettingsService::class);
    $theme        = $settings->group('theme');
    $general      = $settings->group('general');
    $integrations = $settings->group('integrations');
    $isRtl        = app()->getLocale() === 'ar';
@endphp
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ $general['site_name'] ?? config('app.name') }}</title>

    {{-- خط الهوية: Cairo --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- ⚡ Theme Engine: كل الـ tokens بتتحقن من الداشبورد — تغيير لون = بدون build --}}
    <style>
        :root {
        @foreach ($theme as $key => $value)
            --{{ str_replace('_', '-', $key) }}: {{ $value }};
        @endforeach
        }
    </style>

    @if (!empty($integrations['gtm_id']))
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $integrations['gtm_id'] }}');</script>
    @endif

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @inertiaHead
</head>
<body class="font-sans antialiased bg-bg text-text">
    @if (!empty($integrations['gtm_id']))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $integrations['gtm_id'] }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    @inertia
</body>
</html>
