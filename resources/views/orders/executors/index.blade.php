@extends('layouts.app')
@section('title', 'Tron Energy - Исполнители')
@section('content')

    <x-breadcrumbs
        title="Исполнители"
        :parents="[
        [
            'name' => 'Заказы',
            'link' => route('orders.index')
        ],
        [
            'name' => 'Заказ # ' . $order->id,
            'link' => route('orders.show', $order)
        ],
    ]"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Исполнители</div>
        <livewire:orders.executors-table :order=$order>
    </div>
    <livewire:scripts>
@endsection
