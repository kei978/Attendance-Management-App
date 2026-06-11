@extends('layouts.app', [
    'authButtons' => 'none',
])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
    <div class="card">

        {{-- タイトル --}}
        <div class="card__title">
            <h1>会員登録</h1>
        </div>

        <form action="{{ route('register') }}" class="form" method="POST" novalidate>
            @csrf
            <div class="form__group">
                <div class="form__group-title">
                    <h2 class="form__label">名前</h2>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="text" name="name" value="{{ old('name') }}" class="form__input--text-input">
                    </div>
                    @error('name')
                        <div class="form__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
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
                        @if ($message !== 'パスワードと一致しません')
                            <div class="form__error">{{ $message }}</div>
                        @endif
                    @enderror
                </div>
            </div>
            <div class="form__group">
                <div class="form__group-title">
                    <h2 class="form__label">パスワード確認</h2>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="password" name="password_confirmation" class="form__input--text-input">
                    </div>
                    @error('password_confirmation')
                        <div class="form__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="form__button">
                <button class="form__button-submit" type="submit">登録する</button>
            </div>
            <div class="form__link">
                <a href="{{ route('login') }}" class="form__link-item">ログインはこちら</a>
            </div>
        </form>

    </div>
@endsection
