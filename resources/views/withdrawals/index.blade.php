@extends('layouts.app')
@section('title', 'Tron Energy - Заявки на вывод')
@section('content')

    <x-breadcrumbs
            title="Заявки на вывод"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Заявки на вывод</div>
        <livewire:withdrawals.index-table>
    </div>
    <livewire:scripts>
@endsection
