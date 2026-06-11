<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

class AttendanceShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ============================================================
        勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    ============================================================ */
    public function test_detail_page_shows_user_name()
    {
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-01',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee($user->name);
    }

    /* ============================================================
        勤怠詳細画面の「日付」が選択した日付になっている
    ============================================================ */
    public function test_detail_page_shows_correct_date()
    {
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-01',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('2026年6月1日');
    }

    /* ======================================================================
        「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    ====================================================================== */
    public function test_detail_page_shows_clock_in_and_clock_out()
    {
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-01',
            'clock_in' => '2026-06-01 09:00:00',
            'clock_out' => '2026-06-01 18:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /* ==================================================================
        「休憩」にて記されている時間がログインユーザーの打刻と一致している
    ================================================================== */
    /** 10-4： */
    public function test_detail_page_shows_break_times()
    {
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-01',
            'clock_in' => '2026-06-01 09:00:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '12:30:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('12:00');
        $response->assertSee('12:30');
    }
}