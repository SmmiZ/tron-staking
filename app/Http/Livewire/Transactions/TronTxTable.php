<?php

namespace App\Http\Livewire\Transactions;

use App\Http\Livewire\Traits\Sorter;
use App\Models\TronTx;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TronTxTable extends Component
{
    use Sorter;

    public function render(): View
    {
        $transactions = TronTx::query()
            ->orderBy($this->sortField, $this->sortType)
            ->paginate(10);

        return view('livewire.transactions.tron-table', compact('transactions'));
    }
}
