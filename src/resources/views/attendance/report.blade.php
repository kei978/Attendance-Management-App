@extends('layouts.app', [
    'authButtons' => 'user',
])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/report.css') }}">
@endsection

@section('content')
    <article class="report-container">

        {{-- タイトル --}}
        <div class="page-title">
            <h1>マイ勤怠レポート</h1>
        </div>
        <p class="summary-note">過去６ヶ月の勤怠データから集計しています。</p>

        {{-- 基本サマリー --}}
        <h2 class="section-title">基本サマリー</h2>
        <section class="summary-section">
            <div class="summary-item">
                <h3>総労働時間</h3>
                <p>{{ $summary['total_work'] }}</p>
            </div>
            <div class="summary-item">
                <h3>総残業時間</h3>
                <p>{{ $summary['total_overtime'] }}</p>
            </div>
            <div class="summary-item">
                <h3>平均労働時間／日</h3>
                <p>{{ $summary['avg_work_per_day'] }}</p>
            </div>
        </section>

        {{-- 月次推移 --}}
        <section class="monthly-section">
            <h2>月次推移（過去６ヶ月）</h2>
            <table class="monthly-table">
                <tr>
                    <th class="monthly-table__head">月</th>
                    <th class="monthly-table__head">労働時間</th>
                    <th class="monthly-table__head">残業時間</th>
                </tr>
                @foreach ($monthly as $m)
                    <tr>
                        <td class="monthly-table__data">{{ $m['month'] }}</td>
                        <td class="monthly-table__data">{{ $m['work'] }}</td>
                        <td class="monthly-table__data">{{ $m['overtime'] }}</td>
                    </tr>
                @endforeach
            </table>
        </section>

        {{-- 異常検知 --}}
        <section class="alert-section">
            <h2>今月の異常検知</h2>
            <p class="alert-note">基準：始業 09:00／終業 18:00／長時間労働は1日10時間超</p>
            <div class="alert-items">
                <div class="alert-item">
                    <h3>遅刻回数</h3>
                    <p>{{ $alerts['late'] }}回</p>
                </div>
                <div class="alert-item">
                    <h3>早退回数</h3>
                    <p>{{ $alerts['early_leave'] }}回</p>
                </div>
                <div class="alert-item">
                    <h3>長時間労働回数</h3>
                    <p>{{ $alerts['long_work'] }}日</p>
                </div>
            </div>
        </section>

    </article>
@endsection
