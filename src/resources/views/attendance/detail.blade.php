@extends('layouts.app', [
    'authButtons' => 'user',
])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
@endsection

@section('content')
    <article class="attendance-detail-container">

        {{-- タイトル --}}
        <div class="page-title">
            <span class="title-bar"></span>
            <h1>勤怠詳細</h1>
        </div>

        <form action="{{ route('attendance.update_request', $attendance->id) }}" method="POST">
            @csrf
            <table class="detail-table">
                <tr>
                    <th class="detail-table__head">名前</th>
                    <td class="detail-table__data">{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th class="detail-table__head">日付</th>
                    <td class="detail-table__data">{{ $attendance->date->format('Y年n月j日') }}</td>
                </tr>
                <tr>
                    <th class="detail-table__head">出勤・退勤</th>
                    <td class="detail-table__data">
                        <div class="time-range">
                            <input type="time" name="clock_in" class="time-range__input"
                                value="{{ $pendingRequest ? $override['clock_in'] : ($attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>
                            <span>〜</span>
                            <input type="time" name="clock_out" class="time-range__input"
                                value="{{ $pendingRequest ? $override['clock_out'] : ($attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}"
                                {{ $pendingRequest ? 'disabled' : '' }}>
                        </div>
                        @error('clock_in')
                            <div class="error-inline">{{ $message }}</div>
                        @enderror
                        @error('clock_out')
                            <div class="error-inline">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                @foreach ($breaks as $index => $break)
                    <tr>
                        <th class="detail-table__head">休憩{{ $index + 1 }}</th>
                        <td class="detail-table__data">
                            <div class="time-range">
                                <input type="time" name="break_start[]" class="time-range__input"
                                    value="{{ $pendingRequest
                                        ? $override['breaks'][$index]['start'] ?? ''
                                        : ($break->break_start
                                            ? \Carbon\Carbon::parse($break->break_start)->format('H:i')
                                            : '') }}"
                                    {{ $pendingRequest ? 'disabled' : '' }}>
                                <span>〜</span>
                                <input type="time" name="break_end[]" class="time-range__input"
                                    value="{{ $pendingRequest
                                        ? $override['breaks'][$index]['end'] ?? ''
                                        : ($break->break_end
                                            ? \Carbon\Carbon::parse($break->break_end)->format('H:i')
                                            : '') }}"
                                    {{ $pendingRequest ? 'disabled' : '' }}>
                            </div>
                            @error("break_start.$index")
                                <div class="error-inline">{{ $message }}</div>
                            @enderror
                            @error("break_end.$index")
                                <div class="error-inline">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <th class="detail-table__head">備考</th>
                    <td class="detail-table__data">
                        <div class="note-wrapper">
                            <textarea name="note" class="note-input" {{ $pendingRequest ? 'disabled' : '' }}>{{ $pendingRequest ? $pendingRequest->reason : $attendance->note }}</textarea>
                        </div>
                        @error('note')
                            <div class="error-inline">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>

            @if (!$pendingRequest)
                <div class="submit-area">
                    <button type="submit" class="submit-btn">修正</button>
                </div>
            @endif
            @if ($pendingRequest)
                <p class="pending-message">＊承認待ちのため修正はできません。</p>
            @endif
        </form>

    </article>
@endsection
