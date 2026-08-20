<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
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
        'founded_year'  => 'سنة التأسيس',
        'clients_served' => 'عدد العملاء المتعاقدين',
        'team_size'     => 'عدد أفراد الفريق',
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
        'hero_bg_video' => 'فيديو خلفية الهيرو الرئيسي (mp4)',
        'hero_media'    => 'ميديا الهيرو — صورة أو mp4 (مثال: /videos/hero.mp4)',
        'process_media' => 'ميديا قسم الخطوات — صورة أو mp4',
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

    /**
     * نوع كل حقل — أهم من التخمين من القيمة، لأن لون اتمسح كان بيتحوّل
     * لحقل نص عادي ويضيع الـ color picker.
     * الأنواع: color · media · select · textarea · text (الافتراضي)
     */
    public const TYPES = [
        'primary' => 'color',
        'primary_fg' => 'color',
        'primary_hover' => 'color',
        'secondary' => 'color',
        'bg' => 'color',
        'bg_dark' => 'color',
        'surface' => 'color',
        'surface_dark' => 'color',
        'text' => 'color',
        'text_dark' => 'color',
        'muted' => 'color',
        'success' => 'color',
        'danger' => 'color',
        'font_heading' => 'select',
        'font_body' => 'select',
        'hero_variant' => 'select',
        'logo_path' => 'media',
        'video_url' => 'media',
        'video_poster' => 'media',
        'hero_bg_video' => 'media',
        'hero_media' => 'media',
        'process_media' => 'media',
        'meta_description' => 'textarea',
        'address' => 'textarea',
        'tagline' => 'textarea',
    ];

    /** خطوط جوجل اللي بتدعم العربي — بتتحمّل تلقائيًا في app.blade.php */
    public const FONTS = [
        'Cairo', 'Tajawal', 'Almarai', 'Changa', 'El Messiri',
        'IBM Plex Sans Arabic', 'Noto Kufi Arabic', 'Readex Pro', 'Alexandria', 'Rubik',
    ];

    public static function options(): array
    {
        $fonts = array_map(fn ($f) => ['value' => $f, 'label' => $f], self::FONTS);

        return [
            'font_heading' => $fonts,
            'font_body' => $fonts,
            'hero_variant' => [
                ['value' => 'video', 'label' => 'فيديو خلفية + بحث'],
                ['value' => 'webgl', 'label' => 'WebGL ثلاثي الأبعاد'],
                ['value' => 'static', 'label' => 'صورة ثابتة'],
            ],
        ];
    }

    /** شرح تحت الحقل لما يكون محتاج توضيح */
    public const HINTS = [
        'whatsapp' => 'بكود الدولة وبدون + أو مسافات — مثال: 201001234567',
        'founded_year' => 'سنة واحدة زي 2014 — بيتحسب منها «سنة في السوق». سيبها فاضية لو مش عايز تعرضها.',
        'clients_served' => 'رقم حقيقي بس. سيبها فاضية والرقم مش هيظهر في الموقع.',
        'team_size' => 'رقم حقيقي بس. سيبها فاضية والرقم مش هيظهر في الموقع.',
        'radius' => 'استدارة زوايا الأزرار والكروت — مثال: 14px',
        'hero_variant' => 'شكل الهيرو في الصفحة الرئيسية.',
        'video_url' => 'ملف من المكتبة أو رابط يوتيوب.',
        'meta_title' => 'بيظهر في نتيجة البحث وتاب المتصفح لما الصفحة مالهاش عنوان خاص.',
        'meta_description' => 'سطرين تحت العنوان في نتيجة جوجل — 150 حرف تقريبًا.',
        'gtm_id' => 'مثال: GTM-XXXXXXX — بيتحقن في كل صفحات الموقع.',
        'meta_pixel_id' => 'رقم البيكسل من Meta Events Manager.',
        'google_place_id' => 'بيفعّل زرار «قيّمنا على جوجل» و«شوف تقييماتنا».',
    ];

    /**
     * قواعد لكل مفتاح حسب نوعه. مهم أمنيًا: قيم الثيم بتتحقن جوه <style>
     * في app.blade.php، فلو دخلت قيمة حرة ممكن تكسر الصفحة أو تحقن CSS.
     */
    private function valueRules(string $group): array
    {
        $rules = [];
        $options = self::options();

        foreach (array_keys($this->groupKeys($group)) as $key) {
            $rules["values.{$key}"] = match (self::TYPES[$key] ?? 'text') {
                'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
                'select' => ['nullable', 'string', Rule::in(array_column($options[$key] ?? [], 'value'))],
                default => ['nullable', 'string', 'max:2000'],
            };
        }

        // مفيش قيمة CSS حرة: الاستدارة لازم تكون رقم بوحدة
        if ($group === 'theme') {
            $rules['values.radius'] = ['nullable', 'string', 'regex:/^\d{1,3}(px|rem|em|%)$/'];
        }

        return $rules;
    }

    private function valueAttributes(string $group): array
    {
        $attributes = [];

        foreach (array_keys($this->groupKeys($group)) as $key) {
            $attributes["values.{$key}"] = self::LABELS[$key] ?? $key;
        }

        return $attributes;
    }

    private function groupKeys(string $group): array
    {
        return Setting::where('group', $group)->pluck('value', 'key')->toArray();
    }

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
            'types'      => self::TYPES,
            'options'    => self::options(),
            'hints'      => self::HINTS,
        ]);
    }

    public function update(string $group, Request $request, SettingsService $settings): RedirectResponse
    {
        abort_unless(array_key_exists($group, self::GROUPS), 404);

        $data = $request->validate(
            ['values' => ['required', 'array']] + $this->valueRules($group),
            [],
            $this->valueAttributes($group),
        );

        $settings->setMany($group, $data['values']);

        return back()->with('success', 'تم حفظ الإعدادات ✅');
    }
}
