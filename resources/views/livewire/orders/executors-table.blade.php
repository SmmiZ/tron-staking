<div>
    <div class="table col6">
        <div class="table-head">
            <div class="table-row">
                <div><button class="sort-button" wire:click="sort('id')">ID</button></div>
                <div><button class="sort-button" wire:click="sort('user_id')">Исполнитель</button></div>
                <div><button class="sort-button" wire:click="sort('trx_amount')">Кол-во TRX</button></div>
                <div><button class="sort-button" wire:click="sort('resource_amount')">Кол-во ресурса</button></div>
                <div><button class="sort-button" wire:click="sort('resource_amount')">% заказа</button></div>
                <div><button class="sort-button" wire:click="sort('created_at')">Дата передачи ресурсов</button></div>
            </div>
        </div>
        <div class="table-body">
            @forelse($executors as $executor)
                <a href="{{route('users.show', $executor)}}" class="inline-link">
                    <div class="table-row">
                        <div>{{$executor->id}}</div>
                        <div>{{$executor->user->name}}</div>
                        <div>{{$executor->trx_amount}}</div>
                        <div>{{$executor->resource_amount}}</div>
                        <div>{{$executor->resource_amount / $order->resource_amount * 100}}</div>
                        <div>{{$executor->created_at->format('d-m-Y H:i:s')}}</div>
                    </div>
                </a>
            @empty
                <div class="empty">Нет данных</div>
            @endforelse
        </div>
    </div>
    {{$executors->links()}}
</div>
