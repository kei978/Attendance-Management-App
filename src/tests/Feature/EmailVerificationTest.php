<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /* ============================================================
        会員登録後、認証メールが送信される
    ============================================================ */
    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /* ============================================================
        メール認証誘導画面のボタンからメール認証サイトに遷移する
    ============================================================ */
    public function test_verification_notice_page_links_to_mail_verification_site()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/email/verify');
        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');
        $response->assertSee('http://localhost:8025');
    }

    /* ============================================================
        メール認証完了後、勤怠登録画面に遷移する
    ============================================================ */
    public function test_email_verification_redirects_to_attendance_page()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($verificationUrl);
        $response->assertRedirectContains('/attendance');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
