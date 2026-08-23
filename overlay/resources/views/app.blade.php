<!DOCTYPE html>
@php
    /** @var \Modules\Core\Services\SettingsService $settings */
    $settings     = app(\Modules\Core\Services\SettingsService::class);
    $theme        = $settings->group('theme');
    $general      = $settings->group('general');
    $seo          = $settings->group('seo');
    $integrations = $settings->group('integrations');
    $isRtl        = app()->getLocale() === 'ar';

    // الخطوط اللي اتختارت من الداشبورد بتتحمّل من جوجل — مش مثبتة على Cairo
    $fonts = collect([$theme['font_heading'] ?? null, $theme['font_body'] ?? null])
        ->filter()
        ->unique()
        ->map(fn ($f) => 'family='.str_replace(' ', '+', trim($f)).':wght@400;500;600;700;800;900')
        ->implode('&');

    // ميتا الصفحة الحالية جاية من الراوت (App\Support\Seo)، وبتقع على الإعدادات العامة
    $meta        = $page['props']['meta'] ?? [];
    // ?? قبل ?: مطلوبة: مجموعة seo بتبقى فاضية على تثبيت جديد، وكل صفحة
    // مالهاش prop اسمه meta (كل شاشات اللوحة) كانت بتقع 500 من غيرها
    $title       = $meta['title'] ?? (($seo['meta_title'] ?? '') ?: ($general['site_name'] ?? config('app.name')));
    $description = $meta['description'] ?? (($seo['meta_description'] ?? '') ?: ($general['tagline'] ?? ''));
    $canonical   = $meta['canonical'] ?? url()->current();
    // شاشات اللوحة مالهاش prop اسمه meta، فبتقع على نفس حساب App\Support\Seo::image
    $ogFallback  = \App\Support\Seo::image(($seo['og_image'] ?? '') ?: $settings->get('branding', 'logo_path', '/images/logo.png'));
    $ogImage     = $meta['image'] ?? $ogFallback['image'];
    $ogWidth     = $meta['imageWidth'] ?? $ogFallback['imageWidth'];
    $ogHeight    = $meta['imageHeight'] ?? $ogFallback['imageHeight'];
    // اللوجو مربع 512×512، فبيوقع في summary — الكارت الكبير بيتفعّل لما الأدمن
    // يحط صورة عريضة في «صورة معاينة اللينك».
    $ogWide      = $meta['imageIsWide'] ?? $ogFallback['imageIsWide'];
    $ogLocale    = $meta['locale'] ?? (($seo['og_locale'] ?? '') ?: ($isRtl ? 'ar_EG' : 'en_US'));
@endphp
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ $title }}</title>

    @if ($description)
        <meta name="description" content="{{ $description }}">
        <meta property="og:description" content="{{ $description }}">
    @endif

    <link rel="canonical" href="{{ $canonical }}">

    {{-- صفحة زي «تم استلام طلبك» مالهاش لازمة في نتائج البحث.
         follow مقصودة: متتفهرسش، بس عدّي على اللينكات اللي فيها. --}}
    @if (! empty($meta['robots']))
        <meta name="robots" content="{{ $meta['robots'] }}">
    @endif

    @foreach (($meta['alternates'] ?? []) as $altLocale => $altUrl)
        <link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $altUrl }}">
    @endforeach
    @if (! empty($meta['alternates']['ar']))
        <link rel="alternate" hreflang="x-default" href="{{ $meta['alternates']['ar'] }}">
    @endif

    <meta property="og:title" content="{{ $title }}">
    <meta property="og:type" content="{{ $meta['type'] ?? 'website' }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ $ogLocale }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $general['site_name'] ?? $title }}">
    {{-- واتساب بيتخطى الكارت الكبير لو الأبعاد مش مكتوبة، لأنه مبينزّلش الصورة عشان يقيسها.
         الأرقام دي مقيسة من الملف في Seo::image — متتكتبش يدوي. --}}
    @if ($ogWidth && $ogHeight)
        <meta property="og:image:width" content="{{ $ogWidth }}">
        <meta property="og:image:height" content="{{ $ogHeight }}">
    @endif
    @if (! empty($general['site_name']))
        <meta property="og:site_name" content="{{ $general['site_name'] }}">
    @endif
    <meta name="twitter:card" content="{{ $ogWide ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $title }}">
    @if ($description)
        <meta name="twitter:description" content="{{ $description }}">
    @endif
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="{{ $general['site_name'] ?? $title }}">

    @foreach (($meta['jsonLd'] ?? []) as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endforeach

    {{-- خطوط الهوية — بتتغير من شاشة "الهوية والألوان" من غير build --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?{{ $fonts ?: 'family=Cairo:wght@400;500;600;700;800;900' }}&display=swap" rel="stylesheet">

    {{-- ⚡ Theme Engine: كل الـ tokens بتتحقن من الداشبورد — تغيير لون = بدون build.
         التحقق بيحصل عند الحفظ، والفلترة دي طبقة تانية: أي قيمة فيها حرف بيقفل
         الـ style أو بيفتح تعليق CSS بتتشال بدل ما تكسر الصفحة. --}}
    <style>
        :root {
        @foreach ($theme as $key => $value)
            @continue (preg_match('/[<>{};@\\\\]|\*\//', (string) $value))
            --{{ str_replace('_', '-', $key) }}: {{ $value }};
        @endforeach
        }
    </style>

    @if (! empty($integrations['gtm_id']))
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $integrations['gtm_id'] }}');</script>
    @endif

    @if (! empty($integrations['meta_pixel_id']))
        <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{{ $integrations['meta_pixel_id'] }}');fbq('track','PageView');</script>
        <noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id={{ $integrations['meta_pixel_id'] }}&ev=PageView&noscript=1"></noscript>
    @endif

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @inertiaHead
</head>
<body class="font-sans antialiased bg-bg text-text">
    @if (! empty($integrations['gtm_id']))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $integrations['gtm_id'] }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    @inertia
</body>
</html>
