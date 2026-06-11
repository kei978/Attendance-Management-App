@extends('layouts.app', [
    'authButtons' => 'admin',
])

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}">
@endsection

@section('content')
    <article class="attendance-list-container">

        {{-- タイトル --}}
        <div class="page-title">
            <span class="title-bar"></span>
            <h1>スタッフ一覧</h1>
        </div>

        {{-- スタッフ一覧テーブル --}}
        <section class="attendance-list-section">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th class="attendance-table__head">名前</th>
                        <th class="attendance-table__head">メールアドレス</th>
                        <th class="attendance-table__head">月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staff as $user)
                        <tr>
                            <td class="attendance-table__data">{{ $user->name }}</td>
                            <td class="attendance-table__data">{{ $user->email }}</td>
                            <td class="attendance-table__data">
                                <a href="{{ route('admin.attendance.staff.list', ['id' => $user->id, 'month' => now()->format('Y-m')]) }}"
                                    class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

    </article>
@endsection
