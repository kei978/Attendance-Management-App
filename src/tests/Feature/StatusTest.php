<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ============================================================
        勤務外
    ============================================================ */
    public function test_status_is_displayed_as_off_duty()
    {
        $user = User::where('role', 0)->first();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('勤務外');
    }

    /* ============================================================
        出勤中
    ============================================================ */
    public function test_status_is_displayed_as_working()
    {
        $user = User::where('role', 0)->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /* ============================================================
        休憩中
    ============================================================ */
    public function test_status_is_displayed_as_on_break()
    {
        $user = User::where('role', 0)->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
            'status' => 'break',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    /* ============================================================
        退勤済
    ============================================================ */
    public function test_status_is_displayed_as_finished()
    {
        $user = User::where('role', 0)->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'status' => 'after',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }
}