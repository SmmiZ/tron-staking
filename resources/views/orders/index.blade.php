@extends('layouts.app')
@section('title', 'Tron Energy - Заказы')
@section('content')

    <x-breadcrumbs
        title="Заказы"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Заказы</div>
        <livewire:orders.index-table>
    </div>
    <livewire:scripts>
@endsection
