<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /* ============================================================
        会員登録画面表示
    ============================================================ */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /* ============================================================
        会員登録処理
    ============================================================ */
    public function register(RegisterRequest $request)
    {
        // ユーザー作成
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 0,
        ]);

        // 認証メール送信
        $user->sendEmailVerificationNotification();

        // 自動ログイン
        auth()->login($user);

        return redirect()->route('verification.notice');
    }

    /* ============================================================
        ログイン画面表示
    ============================================================ */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /* ============================================================
        ログイン処理
    ============================================================ */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'ログイン情報が登録されていません'])
                ->withInput();
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 未認証ならメール再送
        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice')
                ->with('message', '認証メールを再送しました');
        }

        return redirect()->route('attendance.index');
    }
}