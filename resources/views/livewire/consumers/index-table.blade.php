<div>
    <div class="table col3">
        <div class="table-head">
            <div class="table-row">
                <div><button class="sort-button" wire:click="sort('id')">ID</button></div>
                <div><button class="sort-button" wire:click="sort('name')">Название</button></div>
                <div><button class="sort-button" wire:click="sort('address')">Кошелек</button></div>
            </div>
        </div>
        <div class="table-body">
            @forelse($consumers as $consumer)
                <a href="{{route('consumers.show', $consumer)}}" class="inline-link">
                    <div class="table-row">
                        <div>{{$consumer->id}}</div>
                        <div>{{$consumer->name}}</div>
                        <div>{{$consumer->address}}</div>
                    </div>
                </a>
            @empty
                <div class="empty">Нет данных</div>
            @endforelse
        </div>
    </div>
    {{$consumers->links()}}
</div>
