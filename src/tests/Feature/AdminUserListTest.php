<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;

class AdminUserListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ============================================================
        管理者が全一般ユーザーの氏名・メールアドレスを確認できる
    ============================================================ */
    public function test_admin_can_view_all_users_name_and_email()
    {
        $admin = User::where('role', 1)->first();

        $user1 = User::factory()->create(['role' => 0]);
        $user2 = User::factory()->create(['role' => 0]);

        $response = $this->actingAs($admin)->get('/admin/staff/list');
        $response->assertSee($user1->name);
        $response->assertSee($user1->email);
        $response->assertSee($user2->name);
        $response->assertSee($user2->email);
    }

    /* ============================================================
        ユーザーの勤怠情報が正しく表示される
    ============================================================ */
    public function test_admin_can_view_selected_users_attendance_list()
    {
        $admin = User::where('role', 1)->first();
        $user  = User::factory()->create(['role' => 0]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-01',
            'clock_in' => '09:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}");
        $response->assertSee('06/01');
        $response->assertSee('09:00');
    }

    /* ============================================================
        「前月」ボタンで前月の勤怠が表示される
    ============================================================ */
    public function test_admin_can_view_previous_month_attendance()
    {
        $admin = User::where('role', 1)->first();
        $user  = User::factory()->create(['role' => 0]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'clock_in' => '10:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}?month=2026-05");
        $response->assertSee('05/10');
        $response->assertSee('10:00');
    }

    /* ============================================================
        「翌月」ボタンで翌月の勤怠が表示される
    ============================================================ */
    public function test_admin_can_view_next_month_attendance()
    {
        $admin = User::where('role', 1)->first();
        $user  = User::factory()->create(['role' => 0]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-07-03',
            'clock_in' => '11:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}?month=2026-07");
        $response->assertSee('07/03');
        $response->assertSee('11:00');
    }

    /* ============================================================
        「詳細」ボタンを押下すると勤怠詳細画面に遷移する
    ============================================================ */
    public function test_admin_can_navigate_to_attendance_detail_from_list()
    {
        $admin = User::where('role', 1)->first();
        $user  = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-01',
            'clock_in' => '09:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}");
        $response->assertSee("/admin/attendance/{$attendance->id}");
    }
}