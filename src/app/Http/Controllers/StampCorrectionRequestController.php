<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;

class StampCorrectionRequestController extends Controller
{
    /* ============================================================
        修正申請一覧
    ============================================================ */
    public function list()
    {
        // タブ切り替え（デフォルトは承認待ち）
        $status = request()->query('status', 'pending');

        // ログインユーザーの申請のみ取得
        $requests = AttendanceRequest::with(['user', 'attendance'])
            ->where('user_id', auth()->id())
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('request.list', compact('requests', 'status'));
    }
}
