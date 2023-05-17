<div>
  <div class="filters mb-20">
      <div class="filters-item input-date-range">
          <label for="">Поиск по ID пользователя</label>
          <input type="search" wire:model="userId"/>
      </div>
      <div class="filters-item input-date-range">
          <label for="">Статус</label>
          <select wire:model="status">
              <option selected value="">Не выбрано</option>
              @foreach(\App\Enums\Statuses::cases() as $status)
                  <option name="status" value="{{$status}}">{{$status->translate()}}</option>
              @endforeach
          </select>
      </div>
  </div>
  <div class="table col4">
    <div class="table-head">
      <div class="table-row">
        <div><button class="sort-button">ID пользователя</button></div>
        <div><button class="sort-button" wire:click="sort('trx_amount')">Сумма TRX</button></div>
        <div><button class="sort-button" wire:click="sort('status')">Статус</button></div>
        <div><button class="sort-button" wire:click="sort('created_at')">Дата создания</button></div>
      </div>
    </div>
    <div class="table-body">
      @forelse($withdrawals as $withdrawal)
            <a href="{{ route('withdrawals.show', $withdrawal) }}" class="inline-link">
                <div class="table-row">
                    <div>{{$withdrawal->user_id}}</div>
                    <div>{{$withdrawal->trx_amount}}</div>
                    <div><span class="{{$withdrawal->statusClass}}">{{$withdrawal->status->translate()}}</span></div>
                    <div>{{$withdrawal->created_at}}</div>
                </div>
            </a>
        @empty
            <div class="empty">Нет данных</div>
        @endforelse
    </div>
  </div>
    {{$withdrawals->links()}}
</div>
