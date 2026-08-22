<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Tests\TestCase;

/**
 * استعادة كلمة المرور.
 *
 * أهم حاجة هنا إن الصفحة ماتبقاش أداة يعرف بيها حد مين عنده حساب:
 * الرد واحد سواء الإيميل موجود أو لأ.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->seed(RolePermissionSeeder::class);

        $this->user = User::create([
            'name' => 'عميل',
            'email' => 'user@test.local',
            'password' => 'old-password',
        ]);
        $this->user->syncRoles(['customer']);
    }

    public function test_the_request_page_opens(): void
    {
        $this->get('/ar/forgot-password')->assertOk();
    }

    public function test_it_sends_a_link_for_a_real_account(): void
    {
        $this->post('/ar/forgot-password', ['email' => $this->user->email])
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($this->user, ResetPassword::class);
    }

    public function test_an_unknown_email_gets_the_same_answer(): void
    {
        $response = $this->post('/ar/forgot-password', ['email' => 'nobody@test.local']);

        $response->assertRedirect()->assertSessionHas('success')->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_a_suspended_account_gets_no_link(): void
    {
        $this->user->update(['is_active' => false]);

        $this->post('/ar/forgot-password', ['email' => $this->user->email])
            ->assertSessionHas('success');

        Notification::assertNothingSent();
    }

    public function test_the_link_points_at_our_own_route(): void
    {
        $this->post('/ar/forgot-password', ['email' => $this->user->email]);

        Notification::assertSentTo($this->user, ResetPassword::class, function (ResetPassword $notification) {
            $mail = $notification->toMail($this->user);
            $url = $mail->actionUrl;

            // الافتراضي بيبني على راوت password.reset اللي مالوش بادئة لغة عندنا
            $this->assertStringContainsString('/ar/reset-password/', $url);
            $this->assertStringContainsString('email=user%40test.local', $url);

            // ومكتوبة عربي مش الرسالة الإنجليزية بتاعة لارافيل
            $this->assertStringContainsString('استعادة كلمة المرور', $mail->subject);

            return true;
        });
    }

    public function test_the_reset_page_carries_the_token(): void
    {
        $props = $this->get('/ar/reset-password/some-token?email=user@test.local')
            ->assertOk()
            ->viewData('page')['props'];

        $this->assertSame('some-token', $props['token']);
        $this->assertSame('user@test.local', $props['email']);
    }

    public function test_a_valid_token_changes_the_password(): void
    {
        $token = Password::createToken($this->user);

        $this->post('/ar/reset-password', [
            'token' => $token,
            'email' => $this->user->email,
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertRedirect('/ar/login')->assertSessionHas('success');

        $this->assertTrue(Hash::check('brand-new-pass', $this->user->fresh()->password));
    }

    public function test_the_token_only_works_once(): void
    {
        $token = Password::createToken($this->user);

        $payload = [
            'token' => $token,
            'email' => $this->user->email,
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ];

        $this->post('/ar/reset-password', $payload);
        $this->post('/ar/reset-password', $payload)->assertSessionHasErrors('email');
    }

    public function test_a_forged_token_is_refused(): void
    {
        $this->post('/ar/reset-password', [
            'token' => 'made-up',
            'email' => $this->user->email,
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('old-password', $this->user->fresh()->password));
    }

    public function test_a_short_or_unconfirmed_password_is_refused(): void
    {
        $token = Password::createToken($this->user);

        $this->post('/ar/reset-password', [
            'token' => $token, 'email' => $this->user->email,
            'password' => 'short', 'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');

        $this->post('/ar/reset-password', [
            'token' => $token, 'email' => $this->user->email,
            'password' => 'long-enough-pass', 'password_confirmation' => 'different-pass',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('old-password', $this->user->fresh()->password));
    }

    public function test_a_signed_in_user_is_not_offered_the_page(): void
    {
        $this->actingAs($this->user)->get('/ar/forgot-password')->assertRedirect();
    }
}
