<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Http\Controllers\SettingsController;
use Modules\Core\Services\SettingsService;
use Tests\TestCase;

/**
 * صفحة «من نحن» — محتواها من الإعدادات مش من الكود.
 *
 * الحالة اللي الاختبارات دي بتحرسها: الصفحة كانت بتقول «اثنتا عشرة سنة في
 * سوق واحد» و«فريق من 46 شخص»، وبتعرض أربع صور ستوك بأسماء متلفّقة كأنها
 * الفريق الحقيقي — على موقع عميل حي. القاعدة دلوقتي: **الفاضي بيختفي،
 * مبيتلفّقش.**
 */
class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    private function set(array $values): void
    {
        app(SettingsService::class)->setMany('about', $values);
    }

    public function test_nothing_is_claimed_when_the_admin_wrote_nothing(): void
    {
        $this->get('/ar/about')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('Site/About')
                ->where('content.pledge', [])
                ->where('content.milestones', [])
                ->where('content.team', []));
    }

    public function test_the_old_invented_copy_is_gone_from_the_response(): void
    {
        $html = $this->get('/ar/about')->assertOk()->getContent();

        // العبارات دي بالنص كانت مكتوبة في الكود ومحدش راجعها.
        // بنقيس الجملة مش الرقم لوحده: «46» بتظهر في هاشات ملفات الأصول.
        $claims = [
            'اثنتا عشرة سنة',
            'فريق من 46 شخص',
            'Twelve years in one market',
            'أحمد شلبي',
            'Ahmed Shalaby',
            'team-1.jpg',
            'المكتب الأول',
        ];

        foreach ($claims as $claim) {
            $this->assertStringNotContainsString($claim, $html);
        }
    }

    public function test_written_copy_replaces_the_neutral_default(): void
    {
        $this->set(['hero_title' => 'عنوان الأدمن', 'hero_desc' => 'وصف الأدمن']);

        $this->get('/ar/about')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->where('content.heroTitle', 'عنوان الأدمن')
                ->where('content.heroDesc', 'وصف الأدمن'));
    }

    public function test_the_pledge_is_split_on_blank_lines(): void
    {
        $this->set(['pledge_body' => "فقرة أولى.\n\nفقرة تانية.\n\n\nفقرة تالتة."]);

        $this->get('/ar/about')->assertOk()
            ->assertInertia(fn ($p) => $p->where('content.pledge', [
                'فقرة أولى.', 'فقرة تانية.', 'فقرة تالتة.',
            ]));
    }

    public function test_milestones_and_team_are_parsed_from_pipe_separated_lines(): void
    {
        $this->set([
            'milestones' => "2019 | المكتب الأول | بداية الشغل.\n2023 | التوسّع | فرع تاني.",
            'team' => 'منى | مدير المبيعات | /images/team/mona.jpg',
        ]);

        $this->get('/ar/about')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->where('content.milestones', [
                    ['year' => '2019', 'title' => 'المكتب الأول', 'text' => 'بداية الشغل.'],
                    ['year' => '2023', 'title' => 'التوسّع', 'text' => 'فرع تاني.'],
                ])
                ->where('content.team', [
                    ['name' => 'منى', 'role' => 'مدير المبيعات', 'image' => '/images/team/mona.jpg'],
                ]));
    }

    public function test_an_incomplete_line_is_dropped_instead_of_rendering_a_half_card(): void
    {
        $this->set([
            'team' => "منى | مدير المبيعات | /images/team/mona.jpg\nكريم | مسؤول علاقات\n\nسارة | | /images/team/sara.jpg",
        ]);

        // «كريم» ناقصه الصورة و«سارة» ناقصها الدور — الاتنين بيتسابوا
        $this->get('/ar/about')->assertOk()
            ->assertInertia(fn ($p) => $p->count('content.team', 1));
    }

    public function test_english_uses_its_own_copy_and_falls_back_when_missing(): void
    {
        $this->set([
            'hero_title' => 'عنوان عربي',
            'hero_title_en' => 'English title',
            'hero_desc' => 'وصف عربي بس',
        ]);

        $this->get('/en/about')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->where('content.heroTitle', 'English title')
                // مفيش نسخة إنجليزي — العربي أحسن من فاضي
                ->where('content.heroDesc', 'وصف عربي بس'));
    }

    public function test_the_settings_screen_exposes_the_group(): void
    {
        $this->get('/ar/about')->assertOk();

        $this->assertArrayHasKey('about', SettingsController::GROUPS);
    }
}
