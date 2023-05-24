@extends('layouts.app')
@section('title', 'Tron Energy - Клиент ' . $user->name)
@section('content')

    <x-breadcrumbs
        :title="'Клиент ' . $user->name"
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
                    <h3 class="more-info-title"><span>Информация о клиенте # {{$user->id}}</span></h3>
                    <div class="table col2 lines mb-40">
                        <div class="table-row">
                            <div>Имя</div>
                            <div>{{ $user->name }}</div>
                        </div>
                        <div class="table-row">
                            <div>Код</div>
                            <div>{{ $user->the_code }}</div>
                        </div>
                        <div class="table-row">
                            <div>Лидер</div>
                            <div>{{ $user->leader->id ?? '-' }}</div>
                        </div>
                        <div class="table-row">
                            <div>Уровень</div>
                            <div>{{ $user->level->name_ru }}</div>
                        </div>
                        <div class="table-row">
                            <div>Почта</div>
                            <div>{{ $user->email }}</div>
                        </div>
                        <div class="table-row">
                            <div>Дата регистрации</div>
                            <div>{{ $user->created_at->format('d.m.Y H:i:s') }}</div>
                        </div>
                        <div class="table-row">
                            <div>Сумма стейка</div>
                            <div>{{ $user->stake?->trx_amount ?? 0 }}</div>
                        </div>
                        <div class="table-row">
                            <a href="{{ route('consumers.index', ['userId' => $user->id]) }}" class="inline-link"><u>Потребители</u></a>
                            <div>{{ $user->consumers->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="more-info-btns mb-10">
            <a href="{{ route('users.upload-menu', $user) }}">Загрузка потребителей</a>
        </div>
        <div class="form-button">
            <a href="{{ route('users.index') }}" class="btn-small">Назад</a>
        </div>
    </div>

@endsection
