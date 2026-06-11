<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class AdminAttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-06-06');
        $this->seed();
    }

    /* ============================================================
        承認待ちの修正申請が全て表示されている
    ============================================================ */
    public function test_pending_requests_are_listed_for_admin()
    {
        $admin = User::where('role', 1)->first();

        $user1 = User::factory()->create(['role' => 0]);
        $user2 = User::factory()->create(['role' => 0]);

        $attendance1 = Attendance::factory()->create(['user_id' => $user1->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user2->id]);

        $pending1 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id'       => $user1->id,
            'status'        => 'pending',
            'reason'        => '理由1',
        ]);

        $pending2 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'user_id'       => $user2->id,
            'status'        => 'pending',
            'reason'        => '理由2',
        ]);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list');
        $response->assertSee((string)$pending1->id);
        $response->assertSee((string)$pending2->id);
        $response->assertSee('理由1');
        $response->assertSee('理由2');
    }

    /* ============================================================
        承認済みの修正申請が全て表示されている
    ============================================================ */
    public function test_approved_requests_are_listed_for_admin()
    {
        $admin = User::where('role', 1)->first();

        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $approved1 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'status'        => 'approved',
            'reason'        => '承認1',
        ]);

        $approved2 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'status'        => 'approved',
            'reason'        => '承認2',
        ]);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list?status=approved');
        $response->assertSee((string)$approved1->id);
        $response->assertSee((string)$approved2->id);
        $response->assertSee('承認1');
        $response->assertSee('承認2');
    }

    /* ============================================================
        修正申請の詳細内容が正しく表示されている
    ============================================================ */
    public function test_request_detail_is_displayed_correctly()
    {
        $admin = User::where('role', 1)->first();
        $user  = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'after_value'   => json_encode([
                'clock_in'  => '10:00',
                'clock_out' => '19:00',
                'breaks'    => [],
            ]),
            'reason'        => '遅刻のため修正',
            'status'        => 'pending',
        ]);

        $response = $this->actingAs($admin)->get("/admin/stamp_correction_request/approve/{$request->id}");
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('遅刻のため修正');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /* ============================================================
        修正申請の承認処理が正しく行われる
    ============================================================ */
    public function test_request_is_approved_and_attendance_is_updated()
    {
        $admin = User::where('role', 1)->first();
        $user  = User::factory()->create(['role' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => '2026-06-06',
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'after_value'   => json_encode([
                'clock_in'  => '10:00',
                'clock_out' => '19:00',
                'breaks'    => [],
            ]),
            'status'        => 'pending',
        ]);

        $response = $this->actingAs($admin)->post("/admin/stamp_correction_request/approve/{$request->id}");
        $response->assertStatus(302);

        $this->assertDatabaseHas('attendance_requests', [
            'id'     => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id'        => $attendance->id,
            'clock_in'  => '2026-06-06 10:00:00',
            'clock_out' => '2026-06-06 19:00:00',
        ]);
    }
}