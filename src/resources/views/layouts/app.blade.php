<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Attendance-Management-App</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            {{-- 左：ロゴ --}}
            <div class="header__left">
                <a href="{{ route('login.form') }}" class="header__logo">
                    <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH">
                </a>
            </div>
            {{-- 右：ボタン類 --}}
            <div class="header__right">
                @if (($authButtons ?? 'none') === 'none')
                @elseif(($authButtons ?? 'none') === 'user')
                    <a href="{{ route('attendance.index') }}" class="header__btn">勤怠</a>
                    <a href="{{ route('attendance.list') }}" class="header__btn">勤怠一覧</a>
                    <a href="{{ route('stamp_request.list') }}" class="header__btn">申請</a>
                    <a href="{{ route('attendance.report') }}" class="header__btn">レポート</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="header__btn">ログアウト</button>
                    </form>
                @elseif(($authButtons ?? 'none') === 'admin')
                    <a href="{{ route('admin.attendance.list') }}" class="header__btn">勤怠一覧</a>
                    <a href="{{ route('admin.staff.list') }}" class="header__btn">スタッフ一覧</a>
                    <a href="{{ route('admin.stamp_request.list') }}" class="header__btn">申請一覧</a>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="header__btn">ログアウト</button>
                    </form>
                @endif
            </div>
        </div>
    </header>

    <main class="main">
        @yield('content')
    </main>
</body>

</html>
