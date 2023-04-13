<?php

namespace App\Http\Livewire\Users;

use App\Http\Livewire\Traits\Sorter;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class IndexTable extends Component
{
    use Sorter;

    public function render(): View
    {
        $users = User::query()
            ->orderBy($this->sortField, $this->sortType)
            ->paginate(10);

        return view('livewire.users.index-table', compact('users'));
    }
}
