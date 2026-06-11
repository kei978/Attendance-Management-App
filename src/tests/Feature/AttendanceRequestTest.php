<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

class AttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ============================================================
        出勤時間 > 退勤時間 → エラー
    ============================================================ */
    public function test_error_when_clock_in_is_after_clock_out()
    {
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'clock_in' => '20:00',
            'clock_out' => '10:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '修正依頼',
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
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['20:00'],
            'break_end' => ['21:00'],
            'note' => '修正依頼',
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
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['20:00'],
            'note' => '修正依頼',
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
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
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

    /* ============================================================
        修正申請が作成される
    ============================================================ */
    public function test_correction_request_is_created()
    {
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'break_start' => ['13:00'],
            'break_end' => ['14:00'],
            'note' => '修正依頼',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    /* ============================================================
        承認待ち一覧に自分の申請が表示される
    ============================================================ */
    public function test_pending_requests_are_displayed()
    {
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertSee((string)$request->id);
    }

    /* ============================================================
        承認済み一覧に管理者が承認した申請が表示される
    ============================================================ */
    public function test_approved_requests_are_displayed()
    {
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=approved');
        $response->assertSee('承認済み');
        $response->assertSee($request->user->name);
        $response->assertSee($request->attendance->date->format('Y/m/d'));
        $response->assertSee($request->reason);
    }

    /* ============================================================
        申請の「詳細」→ 勤怠詳細画面に遷移
    ============================================================ */
    public function test_request_detail_navigates_to_attendance_detail()
    {
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get("/stamp_correction_request/list");
        $response->assertSee("/attendance/detail/{$attendance->id}");
    }
}
