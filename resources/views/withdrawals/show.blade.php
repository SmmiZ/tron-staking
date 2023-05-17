@extends('layouts.app')
@section('title', "Tron Energy - Заявка на вывод № $withdrawal->id")
@section('content')

    <x-breadcrumbs
        :title="'Заявка на вывод № ' . $withdrawal->id"
        :parents="[
        [
            'name' => 'Заявки на вывод',
            'link' => route('withdrawals.index')
        ]
    ]"
    ></x-breadcrumbs>

    <div class="box">
        <div class="grid grid-1">
            <div>
                <div class="more-info">
                    <h3 class="more-info-title">
                        <span><b>Заявка на вывод № {{$withdrawal->id}}</b></span>
                    </h3>
                    <div class="table col2 lines mb-40">
                        <div class="table-row">
                            <div class="row-div">ID пользователя</div>
                            <a href="{{ route('users.show', $withdrawal->user_id) }}" class="link">{{ $withdrawal->user_id }}</a>
                        </div>
                        <div class="table-row">
                            <div class="row-div">Адрес кошелька</div>
                            <input class="input" type="text" value="{{ $walletAddress ?? 'Нет данных' }}" disabled>
                        </div>
                        <div class="table-row">
                            <div class="row-div">Сумма TRX</div>
                            <input class="input" name="trx_amount" type="number" value="{{ $withdrawal->trx_amount }}" disabled>
                        </div>
                        <div class="table-row">
                            <div class="row-div">Статус</div>
                            <input class="input" name="status" type="text" value="{{ $withdrawal->status->translate() }}" disabled>
                        </div>
                        <div class="table-row">
                            <div class="row-div">Дата создания</div>
                            <input class="input" name="created_at" type="text" value="{{ $withdrawal->created_at?->format('d.m.Y H:i:s') }}" disabled>
                        </div>
                        <div class="table-row">
                            <div class="row-div">Дата обновления</div>
                            <input class="input" name="updated_at" type="text" value="{{ $withdrawal->updated_at?->format('d.m.Y H:i:s') }}" disabled>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="action-btns">
            @if($withdrawal->status === \App\Enums\Statuses::new)
                <button onclick="acceptWithdrawal()" class="btn accept">Подтвердить</button>
                <button onclick="declineWithdrawal()" class="btn decline">Отклонить</button>
            @endif
        </div>
        <form id="acceptWithdrawal" hidden action="{{route('withdrawals.accept', $withdrawal)}}" method="POST" style="display: none;">
            <input type="hidden" class="pin-confirmation" name="pin">
            @csrf
        </form>
        <form id="declineWithdrawal" hidden action="{{route('withdrawals.decline', $withdrawal)}}" method="POST" style="display: none;">
            <input type="hidden" class="pin-confirmation" name="pin">
            @csrf
        </form>
    </div>

    @push('scripts')
        <script>
            function acceptWithdrawal() {
                submitWithConfimation('acceptWithdrawal');
            }
            function declineWithdrawal() {
                submitWithConfimation('declineWithdrawal');
            }
        </script>
    @endpush

@endsection
