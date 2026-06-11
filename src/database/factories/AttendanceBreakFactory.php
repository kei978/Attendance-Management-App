<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceBreak;
use App\Models\Attendance;

class AttendanceBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = AttendanceBreak::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => '12:00',
            'break_end' => null,
            'break_minutes' => 0,
        ];
    }
}