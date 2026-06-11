@extends('layouts.app', [
    'authButtons' => 'none',
])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card__title">
            <h1>管理者ログイン</h1>
        </div>

        <form action="{{ route('admin.login') }}" class="form" method="POST" novalidate>
            @csrf
            <div class="form__group">
                <div class="form__group-title">
                    <h2 class="form__label">メールアドレス</h2>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="email" name="email" value="{{ old('email') }}" class="form__input--text-input">
                    </div>
                    @error('email')
                        <div class="form__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form__group">
                <div class="form__group-title">
                    <h2 class="form__label">パスワード</h2>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="password" name="password" class="form__input--text-input">
                    </div>
                    @error('password')
                        <div class="form__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form__button">
                <button class="form__button-submit" type="submit">管理者ログインする</button>
            </div>
        </form>
    </div>
@endsection
