<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Models\Setting;
use Modules\Core\Services\SettingsService;

class SettingsController extends Controller
{
    /** المجموعات المسموح تعديلها من الشاشة الديناميكية */
    public const GROUPS = [
        'general'      => 'عام',
        'theme'        => 'الهوية والألوان',
        'branding'     => 'اللوجو والميديا',
        'contact'      => 'بيانات التواصل',
        'social'       => 'السوشيال ميديا',
        'seo'          => 'السيو',
        'integrations' => 'التكاملات',
    ];

    /** ليبلات عربية للمفاتيح — أي مفتاح مش هنا بيظهر باسمه */
    public const LABELS = [
        // general
        'site_name'     => 'اسم المنصة',
        'tagline'       => 'الوصف التعريفي',
        // theme
        'primary'       => 'اللون الأساسي (CTA)',
        'primary_fg'    => 'لون النص فوق الأساسي',
        'primary_hover' => 'الأساسي عند التمرير',
        'secondary'     => 'اللون الثانوي',
        'bg'            => 'خلفية الصفحات الفاتحة',
        'bg_dark'       => 'خلفية الأقسام الداكنة',
        'surface'       => 'خلفية الكروت',
        'surface_dark'  => 'كروت الأقسام الداكنة',
        'text'          => 'لون النص الأساسي',
        'text_dark'     => 'النص على الداكن',
        'muted'         => 'النص الثانوي',
        'success'       => 'لون النجاح',
        'danger'        => 'لون الخطر',
        'radius'        => 'استدارة الزوايا (مثال: 14px)',
        'font_heading'  => 'خط العناوين',
        'font_body'     => 'خط النصوص',
        'hero_variant'  => 'نمط الهيرو (webgl أو static)',
        // branding
        'logo_path'     => 'مسار اللوجو (مثال: /images/logo.png)',
        'video_url'     => 'رابط الفيديو التعريفي (mp4 أو YouTube)',
        'video_poster'  => 'صورة غلاف الفيديو (اختياري)',
        // contact
        'whatsapp'      => 'رقم الواتساب (بكود الدولة بدون +)',
        'phone'         => 'رقم التليفون',
        'email'         => 'البريد الإلكتروني',
        'address'       => 'العنوان',
        // social
        'facebook'      => 'فيسبوك',
        'instagram'     => 'إنستجرام',
        'tiktok'        => 'تيك توك',
        'linkedin'      => 'لينكد إن',
        'x'             => 'إكس (تويتر)',
        'youtube'       => 'يوتيوب',
        'snapchat'      => 'سناب شات',
        // seo
        'meta_title'    => 'عنوان الميتا الافتراضي',
        'meta_description' => 'وصف الميتا الافتراضي',
        // integrations
        'gtm_id'        => 'Google Tag Manager ID',
        'meta_pixel_id' => 'Meta Pixel ID',
        'google_place_id' => 'Google Place ID (للتقييمات)',
    ];

    public function edit(string $group): Response
    {
        abort_unless(array_key_exists($group, self::GROUPS), 404);

        $values = Setting::query()
            ->where('group', $group)
            ->orderBy('id')
            ->pluck('value', 'key')
            ->toArray();

        return Inertia::render('Admin/Settings/Edit', [
            'group'      => $group,
            'groupLabel' => self::GROUPS[$group],
            'groups'     => collect(self::GROUPS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'values'     => $values,
            'labels'     => self::LABELS,
        ]);
    }

    public function update(string $group, Request $request, SettingsService $settings): RedirectResponse
    {
        abort_unless(array_key_exists($group, self::GROUPS), 404);

        $data = $request->validate([
            'values'   => ['required', 'array'],
            'values.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $settings->setMany($group, $data['values']);

        return back()->with('success', 'تم حفظ الإعدادات ✅');
    }
}
