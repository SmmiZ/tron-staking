@extends('layouts.app')
@section('title', 'Tron Energy - Новый заказ')
@section('content')

    <x-breadcrumbs
        title="Новый заказ"
        :parents="[
        [
            'name' => 'Заказы',
            'link' => route('orders.index')
        ]
    ]"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Новый заказ</div>
        <div class="form">
            <form id="createOrderForm" action="{{route('orders.store')}}" method="post">
                <input type="hidden" class="pin-confirmation" name="pin">
                @csrf
                <div class="input-group">
                    <label>Потребитель</label>
                    <select name="consumer_id" required>
                        <option value="" selected>Выберите потребителя</option>
                        @foreach($consumers as $consumer)
                            <option value="{{$consumer->id}}">{{$consumer->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group">
                    <input type="number" name="amount" required>
                    <label>Сумма</label>
                </div>
            </form>
            <div class="form-button">
                <button form="createOrderForm" type="submit" class="btn del">Добавить</button>
                <a href="{{route('orders.index')}}" class="btn-small">Назад</a>
            </div>
        </div>
    </div>

@endsection
