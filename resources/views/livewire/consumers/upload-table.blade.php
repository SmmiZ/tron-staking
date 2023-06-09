<div>
    <div class="filters mb-20">
        <div class="more-info-btns mb-10">
            <form wire:submit.prevent="save">
                <input type="file" wire:model="file">
                @if($file)
                    <button type="submit">Загрузить файл</button>
                @endif
            </form>
        </div>
        @if($consumers->isNotEmpty())
            <div class="more-info-btns mb-10">
                <button type="submit" wire:click="updateConsumers">Обновить БД</button>
                <button type="submit" wire:click="cancel">Отмена</button>
            </div>
        @endif
    </div>
    <div>
        @error('file') <span class="error">{{ $message }}</span> @enderror
        @if (session()->has('message'))
            <div class="alert alert-success">
                <b>{{ session('message') }}</b>
            </div>
        @endif
    </div>
    <div class="table col4">
        <div class="table-head">
            <div class="table-row">
                <div>Адрес</div>
                <div>Добавить</div>
                <div>Удалить</div>
                <div>Оставить</div>
            </div>
        </div>
        <div class="table-body">
            @forelse($consumers as $consumer)
                <div class="table-row">
                    <div>{{$consumer->address}}</div>
                    <div>{{$consumer->add ? '✅' : '❌'}}</div>
                    <div>{{$consumer->remove ? '✅' : '❌'}}</div>
                    <div>{{!$consumer->remove && !$consumer->add ? '✅' : '❌'}}</div>
                </div>
            @empty
                <div class="empty">Нет данных</div>
            @endforelse
        </div>
    </div>
    {{--    {{$consumers->links()}}--}}
</div>

<script>
    window.addEventListener('refresh-page', function () {
        window.location.reload();
    })
</script>
