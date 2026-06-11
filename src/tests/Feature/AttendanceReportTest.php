<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-06-06');
        $this->seed();
    }

    /* ============================================================
        ゲストはレポートページにアクセスできない
    ============================================================ */
    public function test_guest_cannot_access_report_page()
    {
        $response = $this->get('/attendance/report');
        $response->assertRedirect('/login');
    }

    /* ============================================================
        認証ユーザーの統計情報が正しく計算される
    ============================================================ */
    public function test_authenticated_user_gets_correct_statistics()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-05-10',
            'total_work_minutes' => 480,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-02',
            'total_work_minutes' => 601,
        ]);

        $response = $this->actingAs($user)->get('/attendance/report');
        $response->assertSee('18h 1m');
        $response->assertSee('2h 1m');
        $response->assertSee('9h 0m');
        $response->assertSee('2026-05');
        $response->assertSee('2026-06');
        $response->assertSee('1日');
    }

    /* ============================================================
        勤怠記録がないユーザーでも安全に処理される
    ============================================================ */
    public function test_empty_attendance_user_is_safely_processed()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/report');
        $response->assertSee('0h 0m');
        $response->assertSee('2026-01');
        $response->assertSee('0h 0m');
        $response->assertSee('0回');
        $response->assertSee('0日');
    }
}
