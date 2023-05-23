<div>
    <div class="filters mb-20">
        <div class="filters-item input-date-range">
            <label for="">Поиск по ID пользователя</label>
            <input type="search" wire:model="userId"/>
        </div>
    </div>
    <div class="table col7">
        <div class="table-head">
            <div class="table-row">
                <div><button class="sort-button" wire:click="sort('id')">ID</button></div>
                <div><button class="sort-button" wire:click="sort('user_id')">Пользователь</button></div>
                <div><button class="sort-button" wire:click="sort('name')">Название</button></div>
                <div><button class="sort-button" wire:click="sort('address')">Кошелек</button></div>
                <div><button class="sort-button" wire:click="sort('resource_amount')">Нужно энергии</button></div>
                <div><button class="sort-button" wire:click="sort('created_at')">Создан</button></div>
                <div><button class="sort-button" wire:click="sort('updated_at')">Обновлен</button></div>
            </div>
        </div>
        <div class="table-body">
            @forelse($consumers as $consumer)
                <a href="{{route('consumers.show', $consumer)}}" class="inline-link">
                    <div class="table-row">
                        <div>{{$consumer->id}}</div>
                        <div>{{$consumer->user_id == 1 ? 'Системный' : $consumer->user_id}}</div>
                        <div>{{$consumer->name}}</div>
                        <div>{{$consumer->address}}</div>
                        <div>{{$consumer->resource_amount}}</div>
                        <div>{{$consumer->created_at}}</div>
                        <div>{{$consumer->updated_at}}</div>
                    </div>
                </a>
            @empty
                <div class="empty">Нет данных</div>
            @endforelse
        </div>
    </div>
    {{$consumers->links()}}
</div>
