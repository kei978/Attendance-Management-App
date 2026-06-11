<?php

namespace App\Http\Controllers;

use App\Models\Attendance;

class AttendanceReportController extends Controller
{
    /* ============================================================
        マイ勤怠レポート
    ============================================================ */
    public function index()
    {
        $userId = auth()->id();

        // 対象期間：過去6ヶ月（今月含む）
        $startDate = now()->subMonths(5)->startOfMonth();

        $attendances = Attendance::where('user_id', $userId)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        // 基本サマリー（総労働時間・総残業時間・平均労働時間）
        $totalWork = $attendances->sum('total_work_minutes');

        // 8時間（480分）超えた分を残業として集計
        $totalOver = $attendances->sum(function ($a) {
            return max(0, $a->total_work_minutes - 480);
        });

        $daysCount = $attendances->count();

        $summary = [
            'total_work' => $this->formatMinutes($totalWork),
            'total_overtime' => $this->formatMinutes($totalOver),
            'avg_work_per_day' => $daysCount
                ? $this->formatMinutes(intval($totalWork / $daysCount))
                : '0h 0m',
        ];

        // 月次推移（過去6ヶ月）
        $monthly = [];

        // 基準日を「今月の1日」に固定
        $base = now()->startOfMonth();

        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push($base->copy()->subMonths($i)->format('Y-m'));
        }

        foreach ($months as $month) {

            $monthData = $attendances->filter(function ($a) use ($month) {
                return $a->date->format('Y-m') === $month;
            });

            $work = $monthData->sum('total_work_minutes');
            $over = $monthData->sum(function ($a) {
                return max(0, $a->total_work_minutes - 480);
            });

            $monthly[] = [
                'month' => $month,
                'work' => $this->formatMinutes($work),
                'overtime' => $this->formatMinutes($over),
            ];
        }

        // 異常検知（今月）
        $thisMonth = now()->format('Y-m');

        $thisMonthData = $attendances->filter(function ($a) use ($thisMonth) {
            return $a->date->format('Y-m') === $thisMonth;
        });

        $alerts = [
            'late' => $thisMonthData->filter(fn($a) => $a->clock_in && $a->clock_in->format('H:i') > '09:00')->count(),
            'early_leave' => $thisMonthData->filter(fn($a) => $a->clock_out && $a->clock_out->format('H:i') < '18:00')->count(),
            'long_work' => $thisMonthData->filter(fn($a) => $a->total_work_minutes > 600)->count(),
        ];

        return view('attendance.report', compact('summary', 'monthly', 'alerts'));
    }

    /* ============================================================
        分数 → "Xh Ym" に変換
    ============================================================ */
    private function formatMinutes(int $minutes): string
    {
        return floor($minutes / 60) . 'h ' . ($minutes % 60) . 'm';
    }
}