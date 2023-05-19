<?php

namespace App\Http\Livewire\Transactions;

use App\Http\Livewire\Traits\Sorter;
use App\Models\InternalTx;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class InternalTxTable extends Component
{
    use Sorter;

    public function render(): View
    {
        $transactions = InternalTx::query()
            ->orderBy($this->sortField, $this->sortType)
            ->paginate(10);

        return view('livewire.transactions.internal-table', compact('transactions'));
    }
}
