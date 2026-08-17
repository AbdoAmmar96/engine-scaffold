<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [

            'general' => [
                'site_name' => 'المنصة العقارية', // ← الاسم النهائي بيتغير من الداشبورد
                'tagline'   => 'بوابتك الذكية لعقارات مصر',
            ],

            // 🎨 Palette A — "Midnight & Gold" (نسخة فاتحة: الموقع أبيض) + خط Cairo
            'theme' => [
                'primary'       => '#C9A227',
                'primary_fg'    => '#0B1220',
                'primary_hover' => '#B8921F',
                'secondary'     => '#1E3A5F',
                'bg'            => '#FFFFFF',
                'bg_dark'       => '#0B1220',
                'surface'       => '#FFFFFF',
                'surface_dark'  => '#111A2E',
                'text'          => '#0F172A',
                'text_dark'     => '#F1F5F9',
                'muted'         => '#64748B',
                'success'       => '#16A34A',
                'danger'        => '#DC2626',
                'radius'        => '14px',
                'font_heading'  => 'Cairo',
                'font_body'     => 'Cairo',
                'hero_variant'  => 'webgl',
            ],

            // اللوجو الحقيقي متركّب في public/images — والفيديو بيتضاف برابط من الداشبورد
            'branding' => [
                'logo_path'    => '/images/logo.png',
                'video_url'    => '',
                'video_poster' => '',
            ],

            'contact' => [
                'whatsapp' => '',
                'phone'    => '',
                'email'    => '',
                'address'  => '',
            ],

            'social' => [
                'facebook'  => '',
                'instagram' => '',
                'tiktok'    => '',
                'linkedin'  => '',
                'x'         => '',
                'youtube'   => '',
                'snapchat'  => '',
            ],

            'seo' => [
                'meta_title'       => '',
                'meta_description' => '',
            ],

            'integrations' => [
                'gtm_id'          => '',
                'meta_pixel_id'   => '',
                'google_place_id' => '',
            ],
        ];

        foreach ($groups as $group => $values) {
            foreach ($values as $key => $value) {
                Setting::updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value, 'is_public' => true]
                );
            }
        }
    }
}
