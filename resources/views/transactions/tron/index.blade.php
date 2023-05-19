@extends('layouts.app')
@section('title', 'Tron Energy - Внешние транзакции')
@section('content')

    <x-breadcrumbs
        title="Внешние транзакции"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Внешние транзакции</div>
        <livewire:transactions.tron-tx-table>
    </div>
    <livewire:scripts>
@endsection
