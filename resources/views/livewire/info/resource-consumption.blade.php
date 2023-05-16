<div>
    <div class="table col6">
        <div class="table-head">
            <div class="table-row">
                <div><button class="sort-button" wire:click="sort('id')">ID</button></div>
                <div><button class="sort-button" wire:click="sort('consumer_id')">ID потребителя</button></div>
                <div><button class="sort-button" wire:click="sort('day')">Дата</button></div>
                <div><button class="sort-button" wire:click="sort('energy_amount')">Energy</button></div>
                <div><button class="sort-button" wire:click="sort('bandwidth_amount')">Bandwidth</button></div>
                <div><button class="sort-button" wire:click="sort('created_at')">Создан</button></div>
            </div>
        </div>
        <div class="table-body">
            @forelse($records as $record)
                <div class="table-row">
                    <div>{{$record->id}}</div>
                    <div>{{$record->consumer_id}}</div>
                    <div>{{$record->day}}</div>
                    <div>{{$record->energy_amount}}</div>
                    <div>{{$record->bandwidth_amount}}</div>
                    <div>{{$record->created_at}}</div>
                </div>
            @empty
                <div class="empty">Нет данных</div>
            @endforelse
        </div>
    </div>
    {{$records->links()}}
</div>
