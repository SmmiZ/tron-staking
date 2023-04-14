@extends('layouts.app')
@section('title', 'Tron Energy - Заказ № ' . $order->id)
@section('content')

    <x-breadcrumbs
        :title="'Заказ № ' . $order->id"
        :parents="[
        [
            'name' => 'Заказы',
            'link' => route('orders.index')
        ]
    ]"
    ></x-breadcrumbs>

    <div class="box">
        <div class="grid grid-2">
            <div>
                <div class="more-info">
                    <h3 class="more-info-title"><span>Информация о заказе</span></h3>
                    <div class="table col2 lines mb-40">
                        <div class="table-row">
                            <div>ID</div>
                            <div>{{$order->id}}</div>
                        </div>
                        <div class="table-row">
                            <div>Потребитель</div>
                            <div>{{ $order->consumer->name }}</div>
                        </div>
                        <div class="table-row">
                            <div>Кол-во</div>
                            <div>{{ $order->amount }}</div>
                        </div>
                        <div class="table-row">
                            <div>Статус</div>
                            <div>{{ $order->status->translate() }}</div>
                        </div>
                        <div class="table-row">
                            <div>Дата создания</div>
                            <div>{{ $order->created_at->format('d.m.Y H:i:s') }}</div>
                        </div>
                        <div class="table-row">
                            <div>Дата выполнения</div>
                            <div>{{ $order->executed_at?->format('d.m.Y H:i:s') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-button">
            <a href="{{route('orders.index')}}" class="btn-small">Назад</a>
            <button onclick="deleteOrder()" class="btn del">Удалить</button>
        </div>
    </div>

    <form id="deleteOrder" action="{{route('orders.destroy', $order)}}" method="POST">
        <input type="hidden" class="pin-confirmation" name="pin">
        @method('delete')
        @csrf
    </form>

    @push('scripts')
        <script>
            function deleteOrder() {
                submitWithConfimation('deleteOrder');
            }
        </script>
    @endpush
@endsection
