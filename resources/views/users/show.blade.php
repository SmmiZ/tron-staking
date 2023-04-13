@extends('layouts.app')
@section('title', 'Tron Energy - Информация о клиенте ' . $user->name)
@section('content')

    <x-breadcrumbs
        :title="'Информация о ' . $user->name"
        :parents="[
        [
            'name' => 'Клиенты',
            'link' => route('users.index')
        ]
    ]"
    ></x-breadcrumbs>

    <div class="box">
        <div class="grid grid-2">
            <div>
                <div class="more-info">
                    <h3 class="more-info-title"><span>Информация о клиенте</span></h3>
                    <div class="table col2 lines mb-40">
                        <div class="table-row">
                            <div>ID</div>
                            <div>{{$user->id}}</div>
                        </div>
                        <div class="table-row">
                            <div>Имя</div>
                            <div>{{ $user->name }}</div>
                        </div>
                        <div class="table-row">
                            <div>Почта</div>
                            <div>{{ $user->email }}</div>
                        </div>
                        <div class="table-row">
                            <div>Дата регистрации</div>
                            <div>{{ $user->created_at->format('d.m.Y H:i:s') }}</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="form-button">
            <a href="{{ route('users.index') }}" class="btn-small">Назад</a>
        </div>
        {{--    <div class="more-info-btns mb-10">--}}
        {{--        <a href="{{ route('users.wallets', $user) }}">Кошельки</a>--}}
        {{--    </div>--}}
    </div>

@endsection
