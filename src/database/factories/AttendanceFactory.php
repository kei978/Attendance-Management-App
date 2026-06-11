<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => null,
            'clock_out' => null,
            'total_work_minutes' => 0,
            'total_break_minutes' => 0,
            'status' => 'before',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}