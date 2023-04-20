@extends('layouts.app')
@section('title', '7GG - Редактировать потребителя')
@section('content')

    <x-breadcrumbs
        title="Редактировать потребителя"
        :parents="[
        [
            'name' => 'Потребители',
            'link' => route('consumers.index')
        ],
        [
            'name' => $consumer->name,
            'link' => route('consumers.show', $consumer)
        ]
    ]"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Редактировать потребителя</div>
        <div class="form">
            <form id="editConsumerForm" action="{{route('consumers.update', $consumer)}}" method="post">
                <input type="hidden" class="pin-confirmation" name="pin">
                @csrf
                @method('PATCH')
                <div class="input-group">
                    <input type="text" name="name" value="{{$consumer->name}}">
                    <label for="name">Название</label>
                </div>
                <div class="input-group">
                    <input type="text" name="address" value="{{$consumer->address}}">
                    <label for="address">Кошелек</label>
                </div>
                <div class="input-group">
                    <input type="number" name="resource_amount" value="{{$consumer->resource_amount}}">
                    <label for="resource_amount">Кол-во</label>
                </div>
            </form>
            <div class="form-button">
                <button onclick="editConsumer()" class="btn del">Сохранить</button>
                <a href="{{route('consumers.show', $consumer)}}" class="btn-small">Назад</a>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editConsumer() {
                submitWithConfimation('editConsumerForm');
            }
        </script>
    @endpush

@endsection
