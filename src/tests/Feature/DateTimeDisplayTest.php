<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Carbon\Carbon;

class DateTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /* ============================================================
        現在の日時情報が UI と同じ形式で出力されている
    ============================================================ */
    public function test_current_datetime_is_displayed_in_correct_format()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 6, 16, 57));

        $user = User::where('role', 0)->first();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('2026年6月6日(土)');
        $response->assertSee('16:57');
    }
}
