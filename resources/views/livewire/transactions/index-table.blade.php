<div>
    <div class="table col7">
        <div class="table-head">
            <div class="table-row">
                <div><button class="sort-button" wire:click="sort('id')">ID</button></div>
                <div><button class="sort-button" wire:click="sort('wallet_id')">Кошелек</button></div>
                <div><button class="sort-button" wire:click="sort('from')">От кого</button></div>
                <div><button class="sort-button" wire:click="sort('to')">Кому</button></div>
                <div><button class="sort-button" wire:click="sort('type')">Тип</button></div>
                <div><button class="sort-button" wire:click="sort('trx_amount')">Сумма</button></div>
                <div><button class="sort-button" wire:click="sort('tx_id')">TX ID</button></div>
            </div>
        </div>
        <div class="table-body">
            @forelse($transactions as $transaction)
                <a href="{{route('transactions.show', $transaction)}}" class="inline-link">
                    <div class="table-row">
                        <div>{{$transaction->id}}</div>
                        <div>{{$transaction->wallet_id}}</div>
                        <div>{{$transaction->from}}</div>
                        <div>{{$transaction->to}}</div>
                        <div>{{$transaction->type->name}}</div>
                        <div>{{$transaction->trx_amount}}</div>
                        <div>{{$transaction->tx_id}}</div>
                    </div>
                </a>
            @empty
                <div class="empty">Нет данных</div>
            @endforelse
        </div>
    </div>
    {{$transactions->links()}}
</div>
