<?php

namespace App\Http\Livewire\Info;

use App\Http\Livewire\Traits\Sorter;
use App\Models\ResourceConsumption;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ResourceConsumptionTable extends Component
{
    use Sorter;

    public function render(): View
    {
        $records = ResourceConsumption::query()
            ->orderBy($this->sortField, $this->sortType)
            ->paginate(10);

        return view('livewire.info.resource-consumption', compact('records'));
    }
}
