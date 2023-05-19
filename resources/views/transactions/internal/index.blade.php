@extends('layouts.app')
@section('title', 'Tron Energy - Внутренние транзакции')
@section('content')

    <x-breadcrumbs
        title="Внутренние транзакции"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Внутренние транзакции</div>
        <livewire:transactions.internal-tx-table>
    </div>
    <livewire:scripts>
@endsection
