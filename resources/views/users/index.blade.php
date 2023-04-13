@extends('layouts.app')
@section('title', 'Tron Energy - Клиенты')
@section('content')

    <x-breadcrumbs
        title="Клиенты"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Клиенты</div>
        <livewire:users.index-table>
    </div>
    <livewire:scripts>
@endsection
