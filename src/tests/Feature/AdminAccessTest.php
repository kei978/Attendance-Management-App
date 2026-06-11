<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ============================================================
        管理者ログイン：メール未入力でバリデーション
    ============================================================ */
    public function test_admin_login_requires_email()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /* ============================================================
        管理者ログイン：パスワード未入力でバリデーション
    ============================================================ */
    public function test_admin_login_requires_password()
    {
        $response = $this->post('/admin/login', [
            'email' => 'user3@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /* ============================================================
        管理者ログイン：誤った情報でログイン不可
    ============================================================ */
    public function test_admin_login_fails_with_wrong_credentials()
    {
        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /* ============================================================
        管理者は管理画面にアクセスできる
    ============================================================ */
    public function test_admin_can_access_admin_dashboard()
    {
        $admin = User::where('role', 1)->first();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');
        $response->assertStatus(200);
    }

    /* ============================================================
        一般ユーザーは管理画面にアクセスできない
    ============================================================ */
    public function test_normal_user_cannot_access_admin_dashboard()
    {
        $user = User::where('role', 0)->first();

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => '管理者権限がありません',
        ]);
    }
}
