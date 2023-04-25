@extends('layouts.app')
@section('title', 'Tron Energy - Транзакция № ' . $transaction->id)
@section('content')

    <x-breadcrumbs
        :title="'Транзакция № ' . $transaction->id"
        :parents="[
        [
            'name' => 'Транзакции',
            'link' => route('transactions.index')
        ]
    ]"
    ></x-breadcrumbs>

    <div class="box">
        <div class="grid grid-2">
            <div>
                <div class="more-info">
                    <h3 class="more-info-title"><span>Информация о транзакции</span></h3>
                    <div class="table col2 lines mb-40">
                        <div class="table-row">
                            <div>ID</div>
                            <div>{{$transaction->id}}</div>
                        </div>
                        <div class="table-row">
                            <div>От кого</div>
                            <div>{{ $transaction->from }}</div>
                        </div>
                        <div class="table-row">
                            <div>Кому</div>
                            <div>{{ $transaction->to }}</div>
                        </div>
                        <div class="table-row">
                            <div>Тип</div>
                            <div>{{ $transaction->type->translate() }}</div>
                        </div>
                        <div class="table-row">
                            <div>Сумма</div>
                            <div>{{ $transaction->trx_amount }}</div>
                        </div>
                        <div class="table-row">
                            <div>TX ID</div>
                            <div>{{ $transaction->tx_id }}</div>
                        </div>
                        <div class="table-row">
                            <div>Дата создания</div>
                            <div>{{ $transaction->created_at->format('d.m.Y H:i:s') }}</div>
                        </div>
                        <div class="table-row">
                            <div>Дата обновления</div>
                            <div>{{ $transaction->created_at->format('d.m.Y H:i:s') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-button">
            <a href="{{ route('transactions.index') }}" class="btn-small">Назад</a>
        </div>
    </div>

@endsection
