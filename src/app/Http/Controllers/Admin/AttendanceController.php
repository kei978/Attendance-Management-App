<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use App\Http\Requests\AdminAttendanceDetailRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /* ============================================================
        全ユーザー勤怠一覧
    ============================================================ */
    public function list()
    {
        $date = request()->query('date', now()->toDateString());

        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('date', $date)
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.list', compact('attendances', 'date'));
    }

    /* ============================================================
        勤怠詳細
    ============================================================ */
    public function detail($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);

        $breaks = $attendance->breaks->sortBy('break_no')->values();

        while ($breaks->count() < 2) {
            $breaks->push((object)[
                'break_start' => null,
                'break_end'   => null,
            ]);
        }

        $pendingRequest = AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $override = $pendingRequest
            ? json_decode($pendingRequest->after_value, true)
            : null;

        return view('admin.attendance.detail', compact(
            'attendance',
            'breaks',
            'pendingRequest',
            'override'
        ));
    }

    /* ============================================================
        勤怠データの直接更新
    ============================================================ */
    public function update(AdminAttendanceDetailRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        $attendance->clock_in  = $request->clock_in ?: null;
        $attendance->clock_out = $request->clock_out ?: null;

        // 休憩更新（既存を全削除 → 入力値から再構築）
        $attendance->breaks()->delete();

        // 空欄でも必ず2行保存する
        foreach ($request->break_start as $i => $start) {
            $attendance->breaks()->create([
                'break_no'    => $i + 1,
                'break_start' => $start ?: null,
                'break_end'   => $request->break_end[$i] ?: null,
            ]);
        }

        // 休憩合計・勤務合計を再計算して保存
        $attendance->load('breaks');
        $attendance->total_break_minutes = $attendance->calculateBreakMinutes();
        $attendance->total_work_minutes  = $attendance->calculateWorkMinutes();
        $attendance->save();

        // 管理者による直接修正の記録
        AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id'       => auth()->id(),
            'request_type'  => 'admin_update',
            'before_value'  => null,
            'after_value'   => null,
            'reason'        => $request->reason,
            'status'        => 'approved',
        ]);

        return redirect()->route('admin.attendance.list', [
            'date' => $attendance->date->toDateString()
        ])->with('success', '勤怠を修正しました');
    }


    /* ============================================================
        スタッフ別勤怠一覧
    ============================================================ */
    public function staffList($id)
    {
        // 対象スタッフ
        $user = User::findOrFail($id);

        // 現在の月（YYYY-MM）
        $current = Carbon::parse(request('month', now()->format('Y-m')));

        // 前月・翌月
        $prevMonth = $current->copy()->subMonth()->format('Y-m');
        $nextMonth = $current->copy()->addMonth()->format('Y-m');

        // ★ 月の勤怠データ（breaks を必ず読み込む）
        $attendances = Attendance::with('breaks')
            ->where('user_id', $id)
            ->whereYear('date', $current->year)
            ->whereMonth('date', $current->month)
            ->orderBy('date')
            ->get();

        return view('admin.attendance.staff_list', compact(
            'user',
            'attendances',
            'current',
            'prevMonth',
            'nextMonth'
        ));
    }

    /* ============================================================
        スタッフ別勤怠一覧_csv出力
    ============================================================ */
    public function exportStaffCsv($id)
    {
        $user = User::findOrFail($id);

        // 対象月
        $current = \Carbon\Carbon::parse(request('month', now()->format('Y-m')));

        // 勤怠データ取得
        $attendances = Attendance::with('breaks')
            ->where('user_id', $id)
            ->whereYear('date', $current->year)
            ->whereMonth('date', $current->month)
            ->orderBy('date')
            ->get();

        // CSV ファイル名
        $filename = "{$user->name}_{$current->format('Y_m')}_attendance.csv";

        // CSV 出力
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $a) {
                $break = $a->display_break_minutes;
                $work  = $a->display_work_minutes;

                fputcsv($file, [
                    $a->date->format('Y-m-d'),
                    optional($a->clock_in)->format('H:i'),
                    optional($a->clock_out)->format('H:i'),
                    $break > 0 ? sprintf('%d:%02d', floor($break / 60), $break % 60) : '',
                    $work > 0 ? sprintf('%d:%02d', floor($work / 60), $work % 60) : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}