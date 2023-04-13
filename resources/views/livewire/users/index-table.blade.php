<div>
    <div class="table col4">
        <div class="table-head">
            <div class="table-row">
                <div><button class="sort-button" wire:click="sort('id')">ID</button></div>
                <div><button class="sort-button" wire:click="sort('name')">Имя</button></div>
                <div><button class="sort-button" wire:click="sort('email')">Почта</button></div>
                <div><button class="sort-button" wire:click="sort('created_at')">Дата регистрации</button></div>
            </div>
        </div>
        <div class="table-body">
            @forelse($users as $user)
                <a href="{{route('users.show', $user)}}" class="inline-link">
                    <div class="table-row">
                        <div>{{$user->id}}</div>
                        <div>{{$user->name}}</div>
                        <div>{{$user->email}}</div>
                        <div>{{$user->created_at->format('d-m-Y H:i:s')}}</div>
                    </div>
                </a>
            @empty
                <div class="empty">Нет данных</div>
            @endforelse
        </div>
    </div>
    {{$users->links()}}
</div>
