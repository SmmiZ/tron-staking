<?php

namespace App\Http\Livewire\Orders;

use App\Http\Livewire\Traits\Sorter;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class IndexTable extends Component
{
    use Sorter;

    public function render(): View
    {
        $orders = Order::with(['consumer:id,name'])
            ->orderBy($this->sortField, $this->sortType)
            ->paginate(10);

        return view('livewire.orders.index-table', compact('orders'));
    }
}
