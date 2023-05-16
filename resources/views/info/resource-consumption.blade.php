@extends('layouts.app')
@section('title', 'Tron Energy - Статистика ресурсов')
@section('content')

    <x-breadcrumbs
        title="Статистика ресурсов"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Статистика ресурсов</div>
        <livewire:info.resource-consumption-table>
    </div>
    <livewire:scripts>
@endsection
