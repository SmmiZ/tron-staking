@extends('layouts.app')
@section('title', 'Tron Energy - Потребители')
@section('content')

    <x-breadcrumbs
        title="Заказы"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Потребители</div>
        <livewire:consumers.index-table>
    </div>
    <livewire:scripts>
@endsection
