@extends('layouts.app', [
    'authButtons' => 'admin',
])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/staff_list.css') }}">
@endsection

@section('content')
    <article class="attendance-list-container">

        {{-- タイトル --}}
        <div class="page-title">
            <span class="title-bar"></span>
            <h1>{{ $user->name }}さんの勤怠</h1>
        </div>

        {{-- 月切替 --}}
        <header class="attendance-list-header">
            <a href="{{ route('admin.attendance.staff.list', ['id' => $user->id, 'month' => $prevMonth]) }}" class="arrow">←
                前月</a>
            <div class="month-center">
                <span class="calendar-icon">📅</span>
                <span class="month-text">{{ $current->format('Y/m') }}</span>
            </div>
            <a href="{{ route('admin.attendance.staff.list', ['id' => $user->id, 'month' => $nextMonth]) }}"
                class="arrow">翌月 →</a>
        </header>

        {{-- 月次勤怠一覧 --}}
        <section class="attendance-list-section">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th class="attendance-table__head">日付</th>
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
                            <td class="attendance-table__data">
                                {{ $attendance->date->format('m/d') }}（{{ ['日', '月', '火', '水', '木', '金', '土'][$attendance->date->dayOfWeek] }}）
                            </td>
                            <td class="attendance-table__data">
                                {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}
                            </td>
                            <td class="attendance-table__data">
                                {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}
                            </td>
                            <td class="attendance-table__data">
                                {{ $break > 0 ? sprintf('%d:%02d', floor($break / 60), $break % 60) : '' }}
                            </td>
                            <td class="attendance-table__data">
                                {{ $work > 0 ? sprintf('%d:%02d', floor($work / 60), $work % 60) : '' }}
                            </td>
                            <td class="attendance-table__data">
                                <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- CSV 出力 --}}
        <div class="csv-export-wrapper">
            <a href="{{ route('admin.attendance.staff.csv', ['id' => $user->id, 'month' => $current->format('Y-m')]) }}"
                class="csv-export-btn">
                CSV出力
            </a>
        </div>

    </article>
@endsection
