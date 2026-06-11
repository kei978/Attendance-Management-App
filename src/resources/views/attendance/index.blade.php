@extends('layouts.app', [
    'authButtons' => 'user',
])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
    <article class="attendance-container">

        <header class="attendance-header">
            <h2 class="status-label">
                @if ($status === 'before')
                    勤務外
                @elseif ($status === 'working')
                    出勤中
                @elseif ($status === 'break')
                    休憩中
                @elseif ($status === 'after')
                    退勤済
                @endif
            </h2>
            <p class="date-text">{{ $date }}</p>
            <p class="time-text">{{ $time }}</p>
        </header>

        <section class="attendance-actions">
            @if ($status === 'before')
                <form action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <button class="btn btn-black" name="action" value="start">出勤</button>
                </form>
            @elseif ($status === 'working')
                <form action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <button class="btn btn-black" name="action" value="end">退勤</button>
                </form>
                <form action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <button class="btn btn-white" name="action" value="break_start">休憩入</button>
                </form>
            @elseif ($status === 'break')
                <form action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <button class="btn btn-white" name="action" value="break_end">休憩戻</button>
                </form>
            @elseif ($status === 'after')
                <p class="after-text">お疲れ様でした。</p>
            @endif
        </section>

    </article>
@endsection
