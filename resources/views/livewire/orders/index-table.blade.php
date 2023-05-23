<div>
    <div class="table col7">
        <div class="table-head">
            <div class="table-row">
                <div><button class="sort-button" wire:click="sort('id')">ID</button></div>
                <div><button class="sort-button" wire:click="sort('consumer_id')">Потребитель</button></div>
                <div><button class="sort-button" wire:click="sort('resource_amount')">Нужно энергии</button></div>
                <div><button class="sort-button" wire:click="sort('executors_sum_resource_amount')">Передано энергии</button></div>
                <div><button class="sort-button" wire:click="sort('status')">Статус</button></div>
                <div><button class="sort-button" wire:click="sort('created_at')">Создан</button></div>
                <div><button class="sort-button" wire:click="sort('deleted_at')">Закрыт</button></div>
            </div>
        </div>
        <div class="table-body">
            @forelse($orders as $order)
                <a href="{{route('orders.show', $order)}}" class="inline-link">
                    <div class="table-row">
                        <div>{{$order->id}}</div>
                        <div>{{$order->consumer->id}}</div>
                        <div><b>{{$order->resource_amount}}</b></div>
                        <div><b>{{$order->executors_sum_resource_amount}}</b></div>
                        <div>{{$order->status->translate()}}</div>
                        <div>{{$order->created_at->format('d-m-Y H:i:s')}}</div>
                        <div>{{$order->deleted_at?->format('d-m-Y H:i:s')}}</div>
                    </div>
                </a>
            @empty
                <div class="empty">Нет данных</div>
            @endforelse
        </div>
    </div>
    {{$orders->links()}}
</div>
