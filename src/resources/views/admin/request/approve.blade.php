@extends('layouts.app', [
    'authButtons' => 'admin',
])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/request/approve.css') }}">
@endsection

@section('content')
    <article class="attendance-detail-container">

        {{-- タイトル --}}
        <div class="page-title">
            <span class="title-bar"></span>
            <h1>勤怠詳細</h1>
        </div>

        <form action="{{ route('admin.stamp_request.approve.store', $request->id) }}" method="POST">
            @csrf
            <table class="detail-table">
                <tr>
                    <th class="detail-table__head">名前</th>
                    <td class="detail-table__data">{{ $request->user->name }}</td>
                </tr>
                <tr>
                    <th class="detail-table__head">日付</th>
                    <td class="detail-table__data">{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y年n月j日') }}
                    </td>
                </tr>
                <tr>
                    <th class="detail-table__head">出勤・退勤</th>
                    <td class="detail-table__data">
                        {{ $after['clock_in'] ?: '—' }} 〜 {{ $after['clock_out'] ?: '—' }}
                    </td>
                </tr>
                <tr>
                    <th class="detail-table__head">休憩1</th>
                    <td class="detail-table__data">
                        {{ $after['breaks'][0]['start'] ?? '' }}
                        @if (!empty($after['breaks'][0]['start']) || !empty($after['breaks'][0]['end']))
                            〜
                        @endif
                        {{ $after['breaks'][0]['end'] ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th class="detail-table__head">休憩2</th>
                    <td class="detail-table__data">
                        {{ $after['breaks'][1]['start'] ?? '' }}
                        @if (!empty($after['breaks'][1]['start']) || !empty($after['breaks'][1]['end']))
                            〜
                        @endif
                        {{ $after['breaks'][1]['end'] ?? '' }}
                    </td>
                </tr>
                @foreach ($after['breaks'] as $index => $b)
                    @if ($index >= 2 && ($b['start'] || $b['end']))
                        <tr>
                            <th class="detail-table__head">休憩{{ $index + 1 }}</th>
                            <td class="detail-table__data">
                                {{ $b['start'] ?? '' }}
                                @if (!empty($b['start']) || !empty($b['end']))
                                    〜
                                @endif
                                {{ $b['end'] ?? '' }}
                            </td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <th class="detail-table__head">申請理由</th>
                    <td class="detail-table__data">{{ $request->reason }}</td>
                </tr>
            </table>

            <div class="submit-area">
                @if ($request->status === 'approved')
                    <button type="button" class="submit-btn" disabled>承認済み</button>
                @else
                    <button type="submit" class="submit-btn">承認</button>
                @endif
            </div>
        </form>

    </article>
@endsection
