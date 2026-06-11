<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ============================================================
        自分が行った勤怠情報が全て表示されている
    ============================================================ */
    public function test_all_my_attendance_records_are_displayed()
    {
        Carbon::setTestNow('2026-06-10');

        $user = User::where('role', 0)->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-01',
            'clock_in' => '2026-06-01 09:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-02',
            'clock_in' => '2026-06-02 09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('06/01');
        $response->assertSee('06/02');
    }

    /* ============================================================
        現在の月が表示される
    ============================================================ */
    public function test_current_month_is_displayed()
    {
        Carbon::setTestNow('2026-06-15');

        $user = User::where('role', 0)->first();

        $response = $this->actingAs($user)->get('/attendance/list');

        $expectedMonth = Carbon::now()->format('Y/m');

        $response->assertSee($expectedMonth);
    }

    /* ============================================================
        「前月」ボタンで前月が表示される
    ============================================================ */
    public function test_previous_month_button_displays_previous_month()
    {
        Carbon::setTestNow('2026-06-10');

        $user = User::where('role', 0)->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-05-20',
            'clock_in' => '2026-05-20 09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-05');
        $response->assertSee('2026/05');
        $response->assertSee('05/20');
    }

    /* ============================================================
        「翌月」ボタンで翌月が表示される
    ============================================================ */
    public function test_next_month_button_displays_next_month()
    {
        Carbon::setTestNow('2026-06-10');

        $user = User::where('role', 0)->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-07-10',
            'clock_in' => '2026-07-10 09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-07');
        $response->assertSee('2026/07');
        $response->assertSee('07/10');
    }

    /* ============================================================
        「詳細」ボタンで勤怠詳細画面に遷移する
    ============================================================ */
    public function test_detail_button_navigates_to_attendance_detail()
    {
        Carbon::setTestNow('2026-06-10');

        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-01',
            'clock_in' => '2026-06-01 09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee("/attendance/detail/{$attendance->id}");
    }
}