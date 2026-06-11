<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /* ============================================================
        管理者ログイン画面
    ============================================================ */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /* ============================================================
        管理者ログイン処理
    ============================================================ */
    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // 認証試行
        if (Auth::attempt($credentials)) {

            if (auth()->user()->role !== 1) {
                Auth::logout();
                return back()->withErrors([
                    'email' => '管理者権限がありません',
                ]);
            }

            return redirect()->route('admin.attendance.list');
        }

        // 認証失敗
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
