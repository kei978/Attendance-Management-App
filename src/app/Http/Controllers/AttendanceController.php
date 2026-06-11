<?php

namespace App\Http\Controllers;

use App\Models\AttendanceBreak;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Http\Requests\AttendanceDetailRequest;

class AttendanceController extends Controller
{
    /* ============================================================
        勤怠登録画面（出勤前 / 出勤中 / 休憩中 / 退勤済）
    ============================================================ */
    public function index()
    {
        $userId = auth()->id();

        $attendance = Attendance::with('breaks')
            ->where('user_id', $userId)
            ->whereDate('date', today())
            ->first();

        // 状態判定
        if (!$attendance) {
            $status = 'before';
        } elseif ($attendance->clock_in && !$attendance->clock_out) {
            $isBreak = $attendance->breaks->whereNull('break_end')->isNotEmpty();
            $status = $isBreak ? 'break' : 'working';
        } else {
            $status = 'after';
        }

        // 日付と時刻（UI 表示用）
        $weekMap = [
            'Sun' => '日',
            'Mon' => '月',
            'Tue' => '火',
            'Wed' => '水',
            'Thu' => '木',
            'Fri' => '金',
            'Sat' => '土',
        ];

        $carbon = now();
        $week = $weekMap[$carbon->format('D')];
        $date = $carbon->format("Y年n月j日") . "({$week})";
        $time = $carbon->format("H:i");

        return view('attendance.index', compact('status', 'date', 'time'));
    }

    /* ============================================================
        出勤 / 退勤 / 休憩入 / 休憩戻 の登録処理
    ============================================================ */
    public function store(Request $request)
    {
        $userId = auth()->id();
        $today = today();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userId, 'date' => $today],
            ['status' => 'before']
        );

        $action = $request->input('action');

        // 出勤
        if ($action === 'start') {
            $attendance->update([
                'clock_in' => now(),
                'status'   => 'working',
            ]);
            return back();
        }

        // 休憩開始
        if ($action === 'break_start') {
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start'   => now()->format('H:i'),
            ]);
            $attendance->update(['status' => 'break']);
            return back();
        }

        // 休憩終了
        if ($action === 'break_end') {
            $break = AttendanceBreak::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest()
                ->first();

            if ($break) {
                $start = Carbon::parse($break->break_start);
                $end   = Carbon::parse(now()->format('H:i'));

                $break->update([
                    'break_end'     => now()->format('H:i'),
                    'break_minutes' => $start->diffInMinutes($end),
                ]);
            }

            // 休憩合計を再計算
            $attendance->update([
                'total_break_minutes' => $attendance->breaks->sum('break_minutes'),
                'status'              => 'working',
            ]);

            return back();
        }

        // 退勤
        if ($action === 'end') {
            $attendance->update([
                'clock_out' => now(),
                'status'    => 'after',
            ]);

            $workMinutes = $attendance->clock_in->diffInMinutes(now()) - $attendance->total_break_minutes;

            $attendance->update([
                'total_work_minutes' => $workMinutes,
            ]);

            return back();
        }

        return back();
    }

    /* ============================================================
        勤怠一覧
    ============================================================ */
    public function list(Request $request): View
    {
        // 現在の月（クエリパラメータがあればそれを優先）
        $current = $request->query('month')
            ? Carbon::parse($request->query('month'))
            : Carbon::now();

        // breaks を読み込む（合計計算に必要）
        $attendances = Attendance::with(['user', 'breaks'])
            ->where('user_id', auth()->id())
            ->whereYear('date', $current->year)
            ->whereMonth('date', $current->month)
            ->orderBy('date')
            ->get();

        return view('attendance.list', [
            'attendances' => $attendances,
            'current'     => $current,
            'prevMonth'   => $current->copy()->subMonth()->format('Y-m'),
            'nextMonth'   => $current->copy()->addMonth()->format('Y-m'),
        ]);
    }

    /* ============================================================
        勤怠詳細
    ============================================================ */
    public function detail($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $pendingRequest = AttendanceRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $override = $pendingRequest
            ? json_decode($pendingRequest->after_value, true)
            : null;

        $breaks = $attendance->breaks->sortBy('break_no')->values();

        // +1 行追加
        $breaks->push((object)[
            'break_start' => null,
            'break_end'   => null,
        ]);

        return view('attendance.detail', compact('attendance', 'breaks', 'pendingRequest', 'override'));
    }

    /* ============================================================
        勤怠修正申請
    ============================================================ */
    public function requestUpdate(AttendanceDetailRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $before = [
            'clock_in'  => optional($attendance->clock_in)->format('H:i'),
            'clock_out' => optional($attendance->clock_out)->format('H:i'),
            'breaks'    => $attendance->breaks->map(function ($b) {
                return [
                    'start' => $b->break_start ? Carbon::parse($b->break_start)->format('H:i') : null,
                    'end'   => $b->break_end   ? Carbon::parse($b->break_end)->format('H:i') : null,
                ];
            })->values(),
        ];

        $after = [
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'breaks'    => collect($request->break_start)->map(function ($start, $i) use ($request) {
                return [
                    'start' => $start ?: null,
                    'end'   => $request->break_end[$i] ?: null,
                ];
            })->values(),
        ];

        AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id'       => auth()->id(),
            'request_type'  => 'update',
            'before_value'  => json_encode($before),
            'after_value'   => json_encode($after),
            'reason'        => $request->note,
            'status'        => 'pending',
        ]);

        return redirect()
            ->back()
            ->with('success', '修正申請を送信しました（承認待ちです）。');
    }
}