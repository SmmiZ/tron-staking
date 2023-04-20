<div>
    <div class="table col6">
        <div class="table-head">
            <div class="table-row">
                <div><button class="sort-button" wire:click="sort('id')">ID</button></div>
                <div><button class="sort-button" wire:click="sort('consumer_id')">Получатель</button></div>
                <div><button class="sort-button" wire:click="sort('resource_amount')">Кол-во ресурса</button></div>
                <div><button class="sort-button" wire:click="sort('status')">Статус</button></div>
                <div><button class="sort-button" wire:click="sort('created_at')">Создан</button></div>
                <div><button class="sort-button" wire:click="sort('executed_at')">Завершен</button></div>
            </div>
        </div>
        <div class="table-body">
            @forelse($orders as $order)
                <a href="{{route('orders.show', $order)}}" class="inline-link">
                    <div class="table-row">
                        <div>{{$order->id}}</div>
                        <div>{{$order->consumer->name}}</div>
                        <div>{{$order->resource_amount}}</div>
                        <div>{{$order->status->translate()}}</div>
                        <div>{{$order->created_at->format('d-m-Y H:i:s')}}</div>
                        <div>{{$order->executed_at?->format('d-m-Y H:i:s')}}</div>
                    </div>
                </a>
            @empty
                <div class="empty">Нет данных</div>
            @endforelse
        </div>
    </div>
    {{$orders->links()}}
</div>
