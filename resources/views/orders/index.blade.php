@extends('layouts.app')
@section('title', 'Tron Energy - Заказы')
@section('content')

    <x-breadcrumbs
        title="Заказы"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Заказы</div>
        <div class="flex-space mb-40">
            <a class="btn" href="{{route('orders.create')}}">Новый</a>
        </div>
        <livewire:orders.index-table>
    </div>
    <livewire:scripts>
@endsection
