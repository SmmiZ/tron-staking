@extends('layouts.app')
@section('title', 'Tron Energy - Потребитель ' . $consumer->name)
@section('content')

    <x-breadcrumbs
        :title="'Потребитель ' . $consumer->name"
        :parents="[
        [
            'name' => 'Потребители',
            'link' => route('consumers.index')
        ]
    ]"
    ></x-breadcrumbs>

    <div class="box">
        <div class="grid grid-2">
            <div>
                <div class="more-info">
                    <h3 class="more-info-title"><span>Информация о потребителе</span></h3>
                    <div class="table col2 lines mb-40">
                        <div class="table-row">
                            <div>ID</div>
                            <div>{{$consumer->id}}</div>
                        </div>
                        <div class="table-row">
                            <div>Имя</div>
                            <div>{{ $consumer->name }}</div>
                        </div>
                        <div class="table-row">
                            <div>Кол-во ресурса</div>
                            <div>{{ $consumer->resource_amount }}</div>
                        </div>
                        <div class="table-row">
                            <div>Дата создания</div>
                            <div>{{ $consumer->created_at->format('d.m.Y H:i:s') }}</div>
                        </div>
                        <div class="table-row">
                            <div>Дата обновления</div>
                            <div>{{ $consumer->updated_at->format('d.m.Y H:i:s') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="action-btns">
            <a href="{{route('consumers.edit', $consumer)}}" class="btn">Редактировать</a>
            <button onclick="deleteConsumer()" class="btn del">Удалить</button>
        </div>
        <div class="form-button">
            <a href="{{route('consumers.index')}}" class="btn-small">Назад</a>
        </div>
    </div>

    <form id="deleteConsumer" action="{{route('consumers.destroy', $consumer)}}" method="POST">
        <input type="hidden" class="pin-confirmation" name="pin">
        @method('delete')
        @csrf
    </form>

    @push('scripts')
        <script>
            function deleteConsumer() {
                submitWithConfimation('deleteConsumer');
            }
        </script>
    @endpush
@endsection
