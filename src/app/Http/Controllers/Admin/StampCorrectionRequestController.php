<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRequest;

class StampCorrectionRequestController extends Controller
{
    /* ============================================================
        修正申請一覧
    ============================================================ */
    public function list()
    {
        $status = request()->query('status', 'pending');

        $requests = \App\Models\AttendanceRequest::with(['user', 'attendance'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.request.list', compact('requests', 'status'));
    }


    /* ============================================================
        修正申請詳細
    ============================================================ */
    public function approve($id)
    {
        $request = AttendanceRequest::with(['attendance', 'user'])
            ->findOrFail($id);
        $after = json_decode($request->after_value, true);
        $before = json_decode($request->before_value, true);

        return view('admin.request.approve', compact(
            'request',
            'after',
            'before'
        ));
    }


    /* ============================================================
        承認処理
    ============================================================ */
    public function storeApprove($id)
    {
        $request = AttendanceRequest::with('attendance')->findOrFail($id);

        // すでに承認済みなら何もしない
        if ($request->status === 'approved') {
            return back()->with('success', 'すでに承認済みです。');
        }

        // after_value を反映
        $after = json_decode($request->after_value, true);
        $attendance = $request->attendance;

        // 出勤・退勤
        $attendance->clock_in  = $after['clock_in'] ?: null;
        $attendance->clock_out = $after['clock_out'] ?: null;

        // 休憩
        $attendance->breaks()->delete(); // 一旦削除して再登録
        foreach ($after['breaks'] as $b) {
            if ($b['start'] || $b['end']) {
                $attendance->breaks()->create([
                    'break_start' => $b['start'],
                    'break_end'   => $b['end'],
                ]);
            }
        }

        $attendance->save();

        // ステータス更新
        $request->status = 'approved';
        $request->save();

        return back()->with('success', '承認しました。');
    }
}