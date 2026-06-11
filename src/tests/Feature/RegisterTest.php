<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ============================================================
        名前が未入力の場合、バリデーションメッセージが表示される
    ============================================================ */
    public function test_name_is_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /* ============================================================
        メールアドレスが未入力の場合、バリデーションメッセージが表示される
    ============================================================ */
    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => '太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /* ===============================================================
        パスワードが8文字未満の場合、バリデーションメッセージが表示される
    =============================================================== */
    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'name' => '太郎',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /* ============================================================
        パスワードが一致しない場合、バリデーションメッセージが表示される
    ============================================================ */
    public function test_password_confirmation_must_match()
    {
        $response = $this->post('/register', [
            'name' => '太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors([
            'password_confirmation' => 'パスワードと一致しません',
        ]);
    }

    /* ============================================================
        パスワードが未入力の場合、バリデーションメッセージが表示される
    ============================================================ */
    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => '太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /* ============================================================
        フォームに内容が入力されていた場合、データが正常に保存される
    ============================================================ */
    public function test_user_can_register_successfully()
    {
        $response = $this->post('/register', [
            'name' => '太郎',
            'email' => 'taro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/email/verify');

        $this->assertDatabaseHas('users', [
            'email' => 'taro@example.com',
        ]);
    }
}