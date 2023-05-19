<div>
    <div class="table col6">
        <div class="table-head">
            <div class="table-row">
                <div><button class="sort-button" wire:click="sort('id')">ID</button></div>
                <div><button class="sort-button" wire:click="sort('user_id')">ID пользователя</button></div>
                <div><button class="sort-button" wire:click="sort('amount')">Сумма</button></div>
                <div><button class="sort-button" wire:click="sort('received')">Получено</button></div>
                <div><button class="sort-button" wire:click="sort('type')">Тип</button></div>
                <div><button class="sort-button" wire:click="sort('created_at')">Дата</button></div>
            </div>
        </div>
        <div class="table-body">
            @forelse($transactions as $transaction)
                <a href="{{route('transactions.internal.show', $transaction)}}" class="inline-link">
                    <div class="table-row">
                        <div>{{$transaction->id}}</div>
                        <div>{{$transaction->user_id}}</div>
                        <div>{{$transaction->amount}}</div>
                        <div>{{$transaction->received}}</div>
                        <div>{{$transaction->type->translate()}}</div>
                        <div>{{$transaction->created_at->format('d-m-Y H:i:s')}}</div>
                    </div>
                </a>
            @empty
                <div class="empty">Нет данных</div>
            @endforelse
        </div>
    </div>
    {{$transactions->links()}}
</div>
