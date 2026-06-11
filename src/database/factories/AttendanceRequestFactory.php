<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Models\User;

class AttendanceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = AttendanceRequest::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'user_id'       => User::factory(),
            'request_type'  => 'update',
            'before_value'  => json_encode([
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
                'breaks'    => [],
            ]),
            'after_value'   => json_encode([
                'clock_in'  => '10:00',
                'clock_out' => '19:00',
                'breaks'    => [],
            ]),
            'reason'        => 'テスト理由',
            'status'        => 'pending',
        ];
    }
}