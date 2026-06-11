@extends('layouts.app', [
    'authButtons' => 'admin',
])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')
    <article class="attendance-list-container">

        {{-- タイトル --}}
        <div class="page-title">
            <span class="title-bar"></span>
            @php
                $currentDate = \Carbon\Carbon::parse($date);
            @endphp
            <h1>{{ $currentDate->format('Y年n月j日') }}の勤怠</h1>
        </div>

        {{-- 日付切替 --}}
        <header class="attendance-list-header">
            @php
                $prevDate = $currentDate->copy()->subDay()->toDateString();
                $nextDate = $currentDate->copy()->addDay()->toDateString();
            @endphp
            <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="attendance-list-header__nav arrow">← 前日</a>
            <div class="month-center">
                <span class="calendar-icon">📅</span>
                <span class="month-text">{{ $currentDate->format('Y/m/d') }}</span>
            </div>
            <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="attendance-list-header__nav arrow">翌日 →</a>
        </header>

        {{-- 勤怠一覧 --}}
        <section class="attendance-list-section">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th class="attendance-table__head">名前</th>
                        <th class="attendance-table__head">出勤</th>
                        <th class="attendance-table__head">退勤</th>
                        <th class="attendance-table__head">休憩</th>
                        <th class="attendance-table__head">合計</th>
                        <th class="attendance-table__head">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        @php
                            $break = $attendance->display_break_minutes;
                            $work = $attendance->display_work_minutes;
                        @endphp
                        <tr>
                            <td class="attendance-table__data">{{ $attendance->user->name }}</td>
                            <td class="attendance-table__data">{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}</td>
                            <td class="attendance-table__data">{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}</td>
                            <td class="attendance-table__data">
                                {{ $break > 0 ? sprintf('%d:%02d', floor($break / 60), $break % 60) : '' }}
                            </td>
                            <td class="attendance-table__data">
                                {{ $work > 0 ? sprintf('%d:%02d', floor($work / 60), $work % 60) : '' }}
                            </td>
                            <td class="attendance-table__data">
                                <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

    </article>
@endsection
