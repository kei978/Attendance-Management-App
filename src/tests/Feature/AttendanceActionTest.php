<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ============================================================
        出勤ボタンが正しく機能する
    ============================================================ */
    public function test_clock_in_button_works()
    {
        Carbon::setTestNow('2026-01-01 09:00:00');

        $user = User::where('role', 0)->first();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'start',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'clock_in' => '2026-01-01 09:00:00',
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /* ============================================================
        出勤は一日一回のみできる（退勤済ユーザーには出勤ボタンが出ない）
    ============================================================ */
    public function test_clock_in_is_only_once_per_day()
    {
        Carbon::setTestNow('2026-01-01 18:00:00');

        $user = User::where('role', 0)->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-01',
            'clock_in' => '2026-01-01 09:00:00',
            'clock_out' => '2026-01-01 18:00:00',
            'status' => 'after',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertDontSee('出勤');
    }

    /* ============================================================
        出勤時刻が勤怠一覧画面で確認できる
    ============================================================ */
    public function test_clock_in_time_is_shown_in_attendance_list()
    {
        Carbon::setTestNow('2026-01-01 09:00:00');

        $user = User::where('role', 0)->first();

        $this->actingAs($user)->post('/attendance', [
            'action' => 'start',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('09:00');
    }

    /* ============================================================
        休憩ボタンが正しく機能する
    ============================================================ */
    public function test_break_start_button_works()
    {
        Carbon::setTestNow('2026-01-01 12:00:00');

        $user = User::where('role', 0)->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-01',
            'clock_in' => '2026-01-01 09:00:00',
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'break_start',
        ]);
        $response->assertRedirect();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    /* ============================================================
        休憩は一日に何回でもできる（休憩入ボタンが再度表示される）
    ============================================================ */
    public function test_break_can_be_started_multiple_times()
    {
        Carbon::setTestNow('2026-01-01 10:00:00');

        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-01',
            'clock_in' => '2026-01-01 09:00:00',
            'status' => 'working',
        ]);

        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);
        Carbon::setTestNow('2026-01-01 10:30:00');
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    /* ============================================================
        休憩戻ボタンが正しく機能する
    ============================================================ */
    public function test_break_end_button_works()
    {
        Carbon::setTestNow('2026-01-01 12:00:00');

        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-01',
            'clock_in' => '2026-01-01 09:00:00',
            'status' => 'working',
        ]);

        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');

        Carbon::setTestNow('2026-01-01 12:30:00');
        $response = $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);
        $response->assertRedirect();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /* ============================================================
        休憩戻は一日に何回でもできる（休憩戻ボタンが再度表示される）
    ============================================================ */
    public function test_break_end_can_be_done_multiple_times()
    {
        Carbon::setTestNow('2026-01-01 10:00:00');

        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-01',
            'clock_in' => '2026-01-01 09:00:00',
            'status' => 'working',
        ]);

        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);
        Carbon::setTestNow('2026-01-01 10:30:00');
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);

        Carbon::setTestNow('2026-01-01 11:00:00');
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }


    /* ============================================================
        休憩時刻が勤怠一覧画面で確認できる
    ============================================================ */
    public function test_break_times_are_shown_in_attendance_list()
    {
        Carbon::setTestNow('2026-01-01 12:00:00');

        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-01',
            'clock_in' => '2026-01-01 09:00:00',
            'status' => 'working',
        ]);

        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);
        Carbon::setTestNow('2026-01-01 12:30:00');
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('0:30');
    }

    /* ============================================================
        退勤ボタンが正しく機能する
    ============================================================ */
    public function test_clock_out_button_works()
    {
        Carbon::setTestNow('2026-01-01 18:00:00');

        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-01',
            'clock_in' => '2026-01-01 10:00:00',
            'total_break_minutes' => 60,
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'end',
        ]);
        $response->assertRedirect();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    /* ============================================================
        退勤時刻が勤怠一覧画面で確認できる
    ============================================================ */
    public function test_clock_out_time_is_shown_in_attendance_list()
    {
        Carbon::setTestNow('2026-01-01 18:00:00');

        $user = User::where('role', 0)->first();

        Carbon::setTestNow('2026-01-01 10:00:00');
        $this->actingAs($user)->post('/attendance', ['action' => 'start']);

        Carbon::setTestNow('2026-01-01 18:00:00');
        $this->actingAs($user)->post('/attendance', ['action' => 'end']);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('18:00');
    }
}
