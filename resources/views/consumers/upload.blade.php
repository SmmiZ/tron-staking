@extends('layouts.app')
@section('title', 'Tron Energy - Загрузка потребителей')
@section('content')

    <x-breadcrumbs
        title="Загрузка потребителей"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Актуализация потребителей пользователя # {{$user->id}}</div>
        <livewire:consumers.upload-table :userId="$user->id">
    </div>
    <livewire:scripts>
@endsection
