@extends('layouts.app')
@section('title', 'Tron Energy - Добавить покупателя')
@section('content')

    <x-breadcrumbs
        title="Добавить покупателя"
        :parents="[
        [
            'name' => 'Покупатели',
            'link' => route('consumers.index')
        ]
    ]"
    ></x-breadcrumbs>

    <div class="box">
        <div class="box-title">Добавить покупателя</div>
        <div class="form">
            <form id="createConsumerForm" action="{{route('consumers.store')}}" method="post">
                <input type="hidden" class="pin-confirmation" name="pin">
                @csrf
                <div class="input-group">
                    <input type="text" name="name" required>
                    <label>Название</label>
                </div>
                <div class="input-group">
                    <input type="text" name="address" required>
                    <label>Кошелек</label>
                </div>
                <div class="input-group">
                    <input type="text" name="resource" disabled value="{{\App\Enums\Resources::ENERGY->name}}">
                    <label>Ресурс</label>
                </div>
                <div class="input-group">
                    <input type="number" name="amount" required>
                    <label>Количество</label>
                </div>
            </form>
            <div class="form-button">
                <button onclick="createConsumer()" class="btn del">Добавить</button>
                <a href="{{route('consumers.index')}}" class="btn-small">Назад</a>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function createConsumer() {
                submitWithConfimation('createConsumerForm');
            }
        </script>
    @endpush

@endsection