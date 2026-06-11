@extends('layouts.app', [
    'authButtons' => 'admin',
])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/request/list.css') }}">
@endsection

@section('content')
    <article class="attendance-list-container">

        {{-- タイトル --}}
        <div class="page-title">
            <span class="title-bar"></span>
            <h1>申請一覧</h1>
        </div>

        {{-- タブ（承認待ち / 承認済み） --}}
        <header class="attendance-list-header">
            <a href="{{ route('admin.stamp_request.list', ['status' => 'pending']) }}"
                class="tab {{ $status === 'pending' ? 'active' : '' }}">承認待ち</a>
            <a href="{{ route('admin.stamp_request.list', ['status' => 'approved']) }}"
                class="tab {{ $status === 'approved' ? 'active' : '' }}">承認済み</a>
        </header>

        {{-- 申請一覧テーブル --}}
        <section class="attendance-list-section">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th class="attendance-table__head">状態</th>
                        <th class="attendance-table__head">名前</th>
                        <th class="attendance-table__head">対象日時</th>
                        <th class="attendance-table__head">申請理由</th>
                        <th class="attendance-table__head">申請日時</th>
                        <th class="attendance-table__head">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $req)
                        <tr>
                            <td class="attendance-table__data">
                                @if ($req->status === 'pending')
                                    <span class="status pending">承認待ち</span>
                                @elseif ($req->status === 'approved')
                                    <span class="status approved">承認済み</span>
                                @endif
                            </td>
                            <td class="attendance-table__data">{{ $req->user->name }}</td>
                            <td class="attendance-table__data">{{ \Carbon\Carbon::parse($req->attendance->date)->format('Y/m/d') }}</td>
                            <td class="attendance-table__data">{{ $req->reason }}</td>
                            <td class="attendance-table__data">{{ $req->created_at->format('Y/m/d') }}</td>
                            <td class="attendance-table__data">
                                <a href="{{ route('admin.stamp_request.approve', $req->id) }}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

    </article>
@endsection
