<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ============================================================
        勤怠詳細画面に表示されるデータが選択したものになっている
    ============================================================ */
    public function test_admin_can_view_attendance_detail()
    {
        $admin = User::where('role', 1)->first();
        $user  = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-01',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");
        $response->assertSee($user->name);
        $response->assertSee('2026年6月1日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /* ============================================================
        出勤時間 > 退勤時間 → エラー
    ============================================================ */
    public function test_error_when_clock_in_is_after_clock_out()
    {
        $admin = User::where('role', 1)->first();
        $user  = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($admin)->post("/admin/attendance/{$attendance->id}", [
            'clock_in' => '20:00',
            'clock_out' => '10:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '修正',
        ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /* ============================================================
        休憩開始 > 退勤時間 → エラー
    ============================================================ */
    public function test_error_when_break_start_is_after_clock_out()
    {
        $admin = User::where('role', 1)->first();
        $user  = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($admin)->post("/admin/attendance/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['20:00'],
            'break_end' => ['21:00'],
            'note' => '修正',
        ]);

        $response->assertSessionHasErrors([
            'break_start.0' => '休憩時間が不適切な値です',
        ]);
    }

    /* ============================================================
        休憩終了 > 退勤時間 → エラー
    ============================================================ */
    public function test_error_when_break_end_is_after_clock_out()
    {
        $admin = User::where('role', 1)->first();
        $user  = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($admin)->post("/admin/attendance/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['20:00'],
            'note' => '修正',
        ]);

        $response->assertSessionHasErrors([
            'break_end.0' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /* ============================================================
        備考未入力 → エラー
    ============================================================ */
    public function test_error_when_note_is_empty()
    {
        $admin = User::where('role', 1)->first();
        $user  = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($admin)->post("/admin/attendance/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '',
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
