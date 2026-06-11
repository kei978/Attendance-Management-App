@extends('layouts.app', [
    'showSearch' => false,
    'authButtons' => 'none',
])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
    <div class="verify">

        <p class="verify__text">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <div class="verify__button">
            <a href="http://localhost:8025" class="verify__button-link" target="_blank">
                認証はこちらから
            </a>
        </div>

        <form method="POST" action="{{ route('verification.send') }}" class="verify__resend">
            @csrf
            <button type="submit" class="verify__resend-button">
                認証メールを再送する
            </button>
        </form>

    </div>
@endsection
