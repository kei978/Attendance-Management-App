<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;

/* ============================================================
    一般ユーザー 認証
============================================================ */
// 会員登録
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->name('register');

// ログイン
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// ログアウト
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/login');
})->name('logout');


/* ============================================================
    メール認証
============================================================ */
// 認証案内ページ
Route::get('/email/verify', fn() => view('auth.verify-email'))
    ->middleware('auth')
    ->name('verification.notice');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 認証リンククリック後の処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');


/* ============================================================
    一般ユーザー（ログイン + メール認証必須）
============================================================ */
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])
        ->name('attendance.store');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
        ->name('attendance.show');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'requestUpdate'])
        ->name('attendance.update_request');

    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])
        ->name('stamp_request.list');

    Route::get('/attendance/report', [AttendanceReportController::class, 'index'])
        ->name('attendance.report');
});


/* ============================================================
    管理者ログイン
============================================================ */
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])
    ->name('admin.login.form');
Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->name('admin.login');

// 管理者ログアウト
Route::post('/admin/logout', function () {
    auth()->logout();
    return redirect('/admin/login');
})->name('admin.logout');


/* ============================================================
    管理者（ログインのみ / メール認証なし）
============================================================ */
Route::prefix('admin')->middleware(['auth'])->group(function () {

    Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])
        ->name('admin.attendance.list');

    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])
        ->name('admin.attendance.show');
    Route::post('/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');

    Route::get('/staff/list', [StaffController::class, 'list'])
        ->name('admin.staff.list');

    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staffList'])
        ->name('admin.attendance.staff.list');
    Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportStaffCsv'])
        ->name('admin.attendance.staff.csv');

    Route::get('/stamp_correction_request/list', [AdminStampCorrectionRequestController::class, 'list'])
        ->name('admin.stamp_request.list');

    Route::get('/stamp_correction_request/approve/{id}', [AdminStampCorrectionRequestController::class, 'approve'])
        ->name('admin.stamp_request.approve');
    Route::post('/stamp_correction_request/approve/{id}', [AdminStampCorrectionRequestController::class, 'storeApprove'])
        ->name('admin.stamp_request.approve.store');
});