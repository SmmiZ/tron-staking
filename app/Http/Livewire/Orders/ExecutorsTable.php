<?php

namespace App\Http\Livewire\Orders;

use App\Http\Livewire\Traits\Sorter;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ExecutorsTable extends Component
{
    use Sorter;

    public Order $order;

    public function render(): View
    {
        $executors = $this->order->executors()->with(['user'])
            ->orderBy($this->sortField, $this->sortType)
            ->paginate(10);

        return view('livewire.orders.executors-table', compact('executors'));
    }
}
