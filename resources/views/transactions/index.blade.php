@extends('layouts.app')
@section('title', 'Tron Energy - Транзакции')
@section('content')

    <x-breadcrumbs
        title="Транзакции"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Транзакции</div>
        <livewire:transactions.index-table>
    </div>
    <livewire:scripts>
@endsection
