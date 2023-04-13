<?php

namespace App\Http\Livewire\Transactions;

use App\Http\Livewire\Traits\Sorter;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class IndexTable extends Component
{
    use Sorter;

    public function render(): View
    {
        $transactions = Transaction::query()
            ->orderBy($this->sortField, $this->sortType)
            ->paginate(10);

        return view('livewire.transactions.index-table', compact('transactions'));
    }
}
