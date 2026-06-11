<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    /* ============================================================
        スタッフ一覧
    ============================================================ */
    public function list()
    {
        // role = 0 の一般ユーザーのみ取得
        $staff = User::where('role', 0)
            ->orderBy('name')
            ->get();

        return view('admin.staff.list', compact('staff'));
    }
}
