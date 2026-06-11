<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use App\Models\User;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        // user1（一般）
        $user1 = User::where('email', 'user1@example.com')->first()->id;

        // user2（一般）
        $user2 = User::where('email', 'user2@example.com')->first()->id;

        // user3（管理者）
        $user3 = User::where('email', 'user3@example.com')->first()->id;

        // user1（過去 5 ヶ月（各月 15 日））
        for ($m = 1; $m <= 5; $m++) {
            $this->createMonthData(
                userId: $user1,
                month: now()->subMonths($m),
                normalDays: 15,
                overtimeDays: 0,
                lateDays: 0,
                earlyLeaveDays: 0,
                longDays: 0
            );
        }

        // 当月（17 日）
        $this->createMonthData(
            userId: $user1,
            month: now(),
            normalDays: 10,
            overtimeDays: 3,
            lateDays: 2,
            earlyLeaveDays: 1,
            longDays: 1
        );

        // user2（ランダム 30 日）
        $this->createRandomAttendance(userId: $user2, days: 30);

        // user3（ランダム 10 日）
        $this->createRandomAttendance(userId: $user3, days: 10);
    }

    /* ============================================================
        user1_データ生成
    ============================================================ */
    private function createMonthData($userId, $month, $normalDays, $overtimeDays, $lateDays, $earlyLeaveDays, $longDays)
    {
        $date = $month->copy()->startOfMonth();

        // 通常勤務
        for ($i = 0; $i < $normalDays; $i++) {
            $this->createAttendance($userId, $date->copy()->addDays($i), '09:00', '18:00');
        }

        // 残業（10h）
        for ($i = 0; $i < $overtimeDays; $i++) {
            $this->createAttendance($userId, $date->copy()->addDays($normalDays + $i), '09:00', '20:00');
        }

        // 遅刻（7.5h）
        for ($i = 0; $i < $lateDays; $i++) {
            $this->createAttendance($userId, $date->copy()->addDays($normalDays + $overtimeDays + $i), '09:30', '18:00');
        }

        // 早退（7h）
        for ($i = 0; $i < $earlyLeaveDays; $i++) {
            $this->createAttendance($userId, $date->copy()->addDays($normalDays + $overtimeDays + $lateDays + $i), '09:00', '17:00');
        }

        // 長時間労働（12h）
        for ($i = 0; $i < $longDays; $i++) {
            $this->createAttendance($userId, $date->copy()->addDays($normalDays + $overtimeDays + $lateDays + $earlyLeaveDays + $i), '08:00', '21:00');
        }
    }

    /* ============================================================
        user2,user3 用_データ生成
    ============================================================ */
    private function createRandomAttendance($userId, $days)
    {
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i);

            // 出勤時間（8:00〜10:00）
            $clockIn = Carbon::parse($date->format('Y-m-d') . ' ' . rand(8, 10) . ':' . (rand(0, 1) ? '00' : '30'));

            // 退勤時間（17:00〜20:00）
            $clockOut = Carbon::parse($date->format('Y-m-d') . ' ' . rand(17, 20) . ':' . (rand(0, 1) ? '00' : '30'));

            $attendance = Attendance::create([
                'user_id' => $userId,
                'date' => $date->toDateString(),
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'total_break_minutes' => 60,
                'total_work_minutes' => $clockIn->diffInMinutes($clockOut) - 60,
            ]);

            // 休憩（12:00–13:00）
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start' => Carbon::parse($date->format('Y-m-d') . ' 12:00'),
                'break_end' => Carbon::parse($date->format('Y-m-d') . ' 13:00'),
                'break_minutes' => 60,
            ]);
        }
    }

    /* ============================================================
        共通：user1_勤怠作成
    ============================================================ */
    private function createAttendance($userId, $date, $clockIn, $clockOut)
    {
        $dateStr = Carbon::parse($date)->toDateString();

        $attendance = Attendance::create([
            'user_id' => $userId,
            'date' => $dateStr,
            'clock_in' => Carbon::parse("$dateStr $clockIn"),
            'clock_out' => Carbon::parse("$dateStr $clockOut"),
            'total_break_minutes' => 60,
            'total_work_minutes' => Carbon::parse("$dateStr $clockIn")
                ->diffInMinutes(Carbon::parse("$dateStr $clockOut")) - 60,
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse("$dateStr 12:00"),
            'break_end' => Carbon::parse("$dateStr 13:00"),
            'break_minutes' => 60,
        ]);
    }
}