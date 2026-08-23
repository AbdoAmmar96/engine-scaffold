<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Setting;
use Modules\Core\Services\SettingsService;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [

            'general' => [
                'site_name' => 'المنصة العقارية', // ← الاسم النهائي بيتغير من الداشبورد
                'tagline' => 'بوابتك الذكية لعقارات مصر',
                // أرقام الشركة — بتفضل فاضية لحد ما العميل يدخّل أرقامه الحقيقية،
                // والأقسام اللي بتستخدمها بتختفي وهي فاضية.
                // (قبل كده كانت مكتوبة في الكود: 12 سنة · 4780 عميل · 46 فرد)
                'founded_year' => '',
                'clients_served' => '',
                'team_size' => '',
            ],

            // صفحة «من نحن» — كلها فاضية عن قصد.
            // كان محتواها مكتوب في React: «اثنتا عشرة سنة»، «فريق من 46 شخص»،
            // أربع محطات، وأربع صور ستوك بأسماء متلفّقة معروضة كأنها الفريق.
            // القسم اللي مالوش بيانات بيختفي — الادعاء أسوأ من النقص.
            'about' => [
                'hero_title' => '',
                'hero_title_en' => '',
                'hero_desc' => '',
                'hero_desc_en' => '',
                'pledge_title' => '',
                'pledge_title_en' => '',
                'pledge_body' => '',
                'pledge_body_en' => '',
                'milestones' => '',
                'milestones_en' => '',
                'team' => '',
                'team_en' => '',
            ],

            // 🎨 Palette A — "Midnight & Gold" (نسخة فاتحة: الموقع أبيض) + خط Cairo
            'theme' => [
                'primary' => '#C9A227',
                'primary_fg' => '#0B1220',
                'primary_hover' => '#B8921F',
                'secondary' => '#1E3A5F',
                'bg' => '#FFFFFF',
                'bg_dark' => '#0B1220',
                'surface' => '#FFFFFF',
                'surface_dark' => '#111A2E',
                'text' => '#0F172A',
                'text_dark' => '#F1F5F9',
                'muted' => '#64748B',
                'success' => '#16A34A',
                'danger' => '#DC2626',
                'radius' => '14px',
                'font_heading' => 'Cairo',
                'font_body' => 'Cairo',
                'hero_variant' => 'video',
            ],

            // اللوجو الحقيقي متركّب في public/images — والفيديو بيتضاف برابط من الداشبورد
            'branding' => [
                'logo_path' => '/images/logo.png',
                'video_url' => '',
                'video_poster' => '',
                // فيديو أو صورة — FrameMedia بيكتشف الامتداد ويرندر video أو img
                'hero_bg_video' => '/videos/skyline-dawn.mp4',
                // صورة خلفية الهيرو: بتبان قبل ما الفيديو يحمّل، ومع نمط static،
                // ومع prefers-reduced-motion. كانت متكتبة ثابتة في HeroSearch.
                'hero_bg_image' => '/images/demo/hero-bg.jpg',
                'hero_media' => '/images/demo/hero.jpg',
                'process_media' => '/videos/open-door.mp4',
            ],

            'contact' => [
                'whatsapp' => '',
                'phone' => '',
                'email' => '',
                'address' => '',
            ],

            'social' => [
                'facebook' => '',
                'instagram' => '',
                'tiktok' => '',
                'linkedin' => '',
                'x' => '',
                'youtube' => '',
                'snapchat' => '',
            ],

            'seo' => [
                'meta_title' => '',
                'meta_description' => '',
                // صورة معاينة اللينك في واتساب/تليجرام/تويتر — لازم 1200×630.
                // المصدر اللي اتولّدت منه: resources/og/og-default.html
                'og_image' => '/images/og-default.png',
                'og_locale' => 'ar_EG',
            ],

            'integrations' => [
                'gtm_id' => '',
                'meta_pixel_id' => '',
                'google_place_id' => '',
            ],
        ];

        foreach ($groups as $group => $values) {
            foreach ($values as $key => $value) {
                // firstOrCreate مش updateOrCreate: القيم دي قيم افتراضية للتثبيت الجديد.
                // deploy.sh بيشغّل db:seed في كل رفعة، و updateOrCreate كانت بترجّع
                // كل إعدادات العميل (ألوان، أرقام تواصل، بيكسل) للأصل في كل مرة.
                Setting::firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value, 'is_public' => true]
                );
            }
        }

        // بنكتب على الموديل مباشرة، والـ SettingsService كاشه دايم —
        // من غير المسح ده أي seed على موقع شغّال بيسيب القيم القديمة في الكاش
        $settings = app(SettingsService::class);

        foreach (array_keys($groups) as $group) {
            $settings->flush($group);
        }
    }
}
