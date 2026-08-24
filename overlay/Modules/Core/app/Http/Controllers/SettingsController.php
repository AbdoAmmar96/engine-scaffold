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
        'general' => 'عام',
        'theme' => 'الهوية والألوان',
        'branding' => 'اللوجو والميديا',
        'about' => 'صفحة من نحن',
        'contact' => 'بيانات التواصل',
        'social' => 'السوشيال ميديا',
        'seo' => 'السيو',
        'integrations' => 'التكاملات',
    ];

    /** ليبلات عربية للمفاتيح — أي مفتاح مش هنا بيظهر باسمه */
    public const LABELS = [
        // general
        'site_name' => 'اسم المنصة',
        'tagline' => 'الوصف التعريفي',
        'founded_year' => 'سنة التأسيس',
        'clients_served' => 'عدد العملاء المتعاقدين',
        'team_size' => 'عدد أفراد الفريق',
        'blog_enabled' => 'إظهار المدونة',
        // about
        'hero_title' => 'عنوان الصفحة (عربي)',
        'hero_title_en' => 'عنوان الصفحة (إنجليزي)',
        'hero_desc' => 'سطر تحت العنوان (عربي)',
        'hero_desc_en' => 'سطر تحت العنوان (إنجليزي)',
        'pledge_title' => 'عنوان «ما يميّزنا» (عربي)',
        'pledge_title_en' => 'عنوان «ما يميّزنا» (إنجليزي)',
        'pledge_body' => 'نص «ما يميّزنا» (عربي)',
        'pledge_body_en' => 'نص «ما يميّزنا» (إنجليزي)',
        'milestones' => 'المحطات (عربي)',
        'milestones_en' => 'المحطات (إنجليزي)',
        'team' => 'الفريق (عربي)',
        'team_en' => 'الفريق (إنجليزي)',
        // theme
        'primary' => 'اللون الأساسي (CTA)',
        'primary_fg' => 'لون النص فوق الأساسي',
        'primary_hover' => 'الأساسي عند التمرير',
        'secondary' => 'اللون الثانوي',
        'bg' => 'خلفية الصفحات الفاتحة',
        'bg_dark' => 'خلفية الأقسام الداكنة',
        'surface' => 'خلفية البطاقات',
        'surface_dark' => 'بطاقات الأقسام الداكنة',
        'text' => 'لون النص الأساسي',
        'text_dark' => 'النص على الداكن',
        'muted' => 'النص الثانوي',
        'success' => 'لون النجاح',
        'danger' => 'لون الخطر',
        'radius' => 'استدارة الزوايا (مثال: 14px)',
        'font_heading' => 'خط العناوين',
        'font_body' => 'خط النصوص',
        'hero_variant' => 'نمط الهيرو',
        // branding
        'logo_path' => 'مسار اللوجو (مثال: /images/logo.png)',
        'video_url' => 'رابط الفيديو التعريفي (mp4 أو YouTube)',
        'video_poster' => 'صورة غلاف الفيديو (اختياري)',
        'hero_bg_video' => 'فيديو خلفية الهيرو الرئيسي (mp4)',
        'hero_bg_image' => 'صورة خلفية الهيرو',
        'hero_media' => 'ميديا الهيرو — صورة أو mp4 (مثال: /videos/hero.mp4)',
        'process_media' => 'ميديا قسم الخطوات — صورة أو mp4',
        // contact
        'whatsapp' => 'رقم الواتساب (بكود الدولة بدون +)',
        'phone' => 'رقم الهاتف',
        'email' => 'البريد الإلكتروني',
        'address' => 'العنوان',
        // social
        'facebook' => 'فيسبوك',
        'instagram' => 'إنستجرام',
        'tiktok' => 'تيك توك',
        'linkedin' => 'لينكد إن',
        'x' => 'إكس (تويتر)',
        'youtube' => 'يوتيوب',
        'snapchat' => 'سناب شات',
        // seo
        'meta_title' => 'عنوان الميتا الافتراضي',
        'meta_description' => 'وصف الميتا الافتراضي',
        'og_image' => 'صورة معاينة الرابط (1200×630)',
        'og_locale' => 'لغة/دولة المعاينة',
        // integrations
        'gtm_id' => 'Google Tag Manager ID',
        'meta_pixel_id' => 'Meta Pixel ID',
        'google_place_id' => 'Google Place ID (للتقييمات)',
    ];

    /**
     * نوع كل حقل — أهم من التخمين من القيمة، لأن لون اتمسح كان بيتحوّل
     * لحقل نص عادي ويضيع الـ color picker.
     * الأنواع: color · media · select · textarea · text (الافتراضي)
     */
    public const TYPES = [
        'blog_enabled' => 'toggle',
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
        'og_image' => 'media',
        'og_locale' => 'select',
        'video_url' => 'media',
        'video_poster' => 'media',
        'hero_bg_video' => 'media',
        'hero_bg_image' => 'media',
        'hero_media' => 'media',
        'process_media' => 'media',
        'meta_description' => 'textarea',
        'address' => 'textarea',
        'tagline' => 'textarea',
        'hero_desc' => 'textarea',
        'hero_desc_en' => 'textarea',
        'pledge_body' => 'textarea',
        'pledge_body_en' => 'textarea',
        'milestones' => 'textarea',
        'milestones_en' => 'textarea',
        'team' => 'textarea',
        'team_en' => 'textarea',
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
            'og_locale' => [
                ['value' => 'ar_EG', 'label' => 'العربية — مصر'],
                ['value' => 'ar_SA', 'label' => 'العربية — السعودية'],
                ['value' => 'ar_AE', 'label' => 'العربية — الإمارات'],
                ['value' => 'ar_KW', 'label' => 'العربية — الكويت'],
                ['value' => 'ar_QA', 'label' => 'العربية — قطر'],
            ],
        ];
    }

    /** شرح تحت الحقل لما يكون محتاج توضيح */
    public const HINTS = [
        'whatsapp' => 'بكود الدولة وبدون + أو مسافات — مثال: 201001234567',
        'blog_enabled' => 'وهي مقفولة تختفي المدونة من القائمة ومن خريطة الموقع، ويرجع رابطها 404. المقالات تفضل محفوظة وتقدر تكتبها وتجهّزها من «المدونة» في اللوحة قبل ما تفتح القسم.',
        'founded_year' => 'سنة واحدة مثل 2014 — تُحتسب منها «سنة في السوق». اتركها فارغة إن لم ترغب في عرضها.',
        'clients_served' => 'رقم حقيقي فقط. اتركها فارغة ولن يظهر الرقم في الموقع.',
        'team_size' => 'رقم حقيقي فقط. اتركها فارغة ولن يظهر الرقم في الموقع.',
        'hero_title' => 'اتركه فارغًا ليُستخدم «من نحن». لا تكتب عدد سنوات أو حجم فريق إلا إن كان حقيقيًا.',
        'pledge_body' => 'فقرة في كل سطر (سطر فارغ بينها). يختفي القسم كله إن تركته فارغًا.',
        'pledge_body_en' => 'فقرة في كل سطر (سطر فارغ بينها).',
        'milestones' => 'محطة في كل سطر بهذا الشكل: 2014 | المكتب الأول | وصف قصير — يختفي القسم إن كان فارغًا.',
        'milestones_en' => 'نفس الشكل: 2014 | The first office | short description',
        'team' => 'فرد في كل سطر: الاسم | الدور | مسار الصورة — يختفي القسم إن كان فارغًا. لا تضع صور ستوك بأسماء ملفّقة.',
        'team_en' => 'نفس الشكل: Name | Role | /images/team/1.jpg',
        'radius' => 'استدارة زوايا الأزرار والبطاقات — مثال: 14px',
        'hero_variant' => 'شكل خلفية الهيرو في الرئيسية. «فيديو خلفية» يشغّل فيديو خلفية الهيرو بالأسفل — والنمطان الآخران يتجاهلانه.',
        'hero_bg_image' => 'تظهر قبل تحميل الفيديو، ومع نمط «صورة ثابتة»، وللزوّار الذين فعّلوا تقليل الحركة. اختر صورة عريضة.',
        'video_url' => 'ملف من المكتبة أو رابط يوتيوب.',
        'meta_title' => 'يظهر في نتيجة البحث وتبويب المتصفح عندما لا يكون للصفحة عنوان خاص.',
        'meta_description' => 'سطرين تحت العنوان في نتيجة جوجل — 150 حرف تقريبًا.',
        'og_image' => 'الصورة التي تظهر عند إرسال رابط الموقع في واتساب أو تليجرام أو تويتر. يجب أن تكون 1200×630 — إن تركتها فارغة يُستخدم اللوجو، واللوجو مربّع فتظهر بطاقة صغيرة دون صورة كبيرة.',
        'og_locale' => 'تُخبر فيسبوك/واتساب أي سوق يخاطبه الموقع.',
        'gtm_id' => 'مثال: GTM-XXXXXXX — يُحقَن في كل صفحات الموقع.',
        'meta_pixel_id' => 'رقم البيكسل من Meta Events Manager.',
        'google_place_id' => 'يفعّل زرَّي «قيّمنا على جوجل» و«اقرأ التقييمات».',
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
                'toggle' => ['nullable', 'string', Rule::in(['0', '1'])],
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
            'group' => $group,
            'groupLabel' => self::GROUPS[$group],
            'groups' => collect(self::GROUPS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'values' => $values,
            'labels' => self::LABELS,
            'types' => self::TYPES,
            'options' => self::options(),
            'hints' => self::HINTS,
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
