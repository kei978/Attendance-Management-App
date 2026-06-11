<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ============================================================
        その日になされた全ユーザーの勤怠情報が正確に確認できる
    ============================================================ */
    public function test_admin_can_view_all_users_attendance_for_today()
    {
        Carbon::setTestNow('2026-06-06');

        $admin = User::where('role', 1)->first();

        $user1 = User::factory()->create(['role' => 0]);
        $user2 = User::factory()->create(['role' => 0]);

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => '2026-06-06',
            'clock_in' => '09:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => '2026-06-06',
            'clock_in' => '10:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertSee('09:00');
        $response->assertSee('10:00');
    }

    /* ============================================================
        遷移した際に現在の日付が表示される
    ============================================================ */
    public function test_current_date_is_displayed()
    {
        Carbon::setTestNow('2026-06-06');

        $admin = User::where('role', 1)->first();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');
        $response->assertSee('2026年6月6日');
    }

    /* ============================================================
        「前日」を押下した時に前の日の勤怠情報が表示される
    ============================================================ */
    public function test_previous_day_attendance_is_displayed()
    {
        Carbon::setTestNow('2026-06-06');

        $admin = User::where('role', 1)->first();
        $user = User::factory()->create(['role' => 0]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-05',
            'clock_in' => '09:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-06-05');
        $response->assertSee('2026年6月5日');
        $response->assertSee('09:00');
    }

    /* ============================================================
        「翌日」を押下した時に次の日の勤怠情報が表示される
    ============================================================ */
    public function test_next_day_attendance_is_displayed()
    {
        Carbon::setTestNow('2026-06-06');

        $admin = User::where('role', 1)->first();
        $user = User::factory()->create(['role' => 0]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-07',
            'clock_in' => '11:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-06-07');
        $response->assertSee('2026年6月7日');
        $response->assertSee('11:00');
    }
}
