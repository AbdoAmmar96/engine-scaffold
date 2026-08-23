<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Scheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Tests\TestCase;

/**
 * نبضة الجدولة.
 *
 * الحالة اللي الاختبارات دي بتحرسها حصلت فعلًا: الـ cron ما اتضافش على
 * الإنتاج، وتنبيهات البحث المحفوظ فضلت واقفة أسابيع من غير أي إشارة —
 * لأن موقع سليم الشكل مبيقولش إن اللي وراه واقف. المؤشّر ده لازم يفضل
 * صادق في الاتجاهين: يحذّر لما تقف، ويسكت لما تشتغل.
 */
class SchedulerHealthTest extends TestCase
{
    use RefreshDatabase;

    private string $beat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->beat = storage_path('app/schedule-heartbeat');
        @unlink($this->beat);
    }

    protected function tearDown(): void
    {
        @unlink($this->beat);

        parent::tearDown();
    }

    public function test_missing_heartbeat_means_never_ran(): void
    {
        $status = Scheduler::status();

        $this->assertFalse($status['healthy']);
        $this->assertFalse($status['ever_ran']);
        $this->assertNull($status['minutes']);
    }

    public function test_beat_marks_the_scheduler_healthy(): void
    {
        Scheduler::beat();

        $this->assertTrue(Scheduler::isHealthy());
        $this->assertSame(0, Scheduler::status()['minutes']);
    }

    public function test_old_heartbeat_is_reported_stale(): void
    {
        Scheduler::beat();

        // ساعتين ورا: كرون وقف من ساعتين، مش كرون اتأخر دقيقة
        touch($this->beat, time() - 7200);

        $status = Scheduler::status();

        $this->assertFalse($status['healthy']);
        $this->assertTrue($status['ever_ran']);
        $this->assertSame(120, $status['minutes']);
    }

    public function test_a_late_beat_inside_the_grace_window_is_still_healthy(): void
    {
        Scheduler::beat();
        touch($this->beat, time() - (Scheduler::STALE_AFTER - 30));

        $this->assertTrue(Scheduler::isHealthy());
    }

    public function test_cron_line_points_at_the_wrapper_script(): void
    {
        // السطر بينده cron.sh مش php مباشرة — مسار الـ PHP بيختلف من استضافة
        // للتانية، والسكريبت بيحلّه لوحده
        $this->assertStringEndsWith('cron.sh', Scheduler::cronLine());
        $this->assertStringStartsWith('* * * * *', Scheduler::cronLine());
        $this->assertFileExists(base_path('cron.sh'));
    }

    public function test_dashboard_warns_the_admin_but_not_the_broker(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = $this->userWithRole('admin');
        $broker = $this->userWithRole('broker');

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('scheduler.healthy', false));

        // الوسيط مش بيقدر يضيف cron — تحذير محدش يتصرّف فيه بيتحوّل لضوضاء
        $this->actingAs($broker)->get('/admin')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('scheduler', null));
    }

    public function test_dashboard_stops_warning_once_the_scheduler_runs(): void
    {
        $this->seed(RolePermissionSeeder::class);

        Scheduler::beat();

        $this->actingAs($this->userWithRole('admin'))->get('/admin')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('scheduler.healthy', true));
    }

    private function userWithRole(string $role): User
    {
        $user = User::create([
            'name' => $role,
            'email' => "{$role}@test.local",
            'password' => 'password123',
        ]);

        $user->syncRoles([$role]);

        return $user;
    }
}
