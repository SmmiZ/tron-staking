<?php

namespace App\Http\Livewire\Consumers;

use App\Http\Livewire\Traits\Sorter;
use App\Models\Consumer;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class IndexTable extends Component
{
    use Sorter;

    public $userId;

    /**
     * Забираем из get-параметров данные для поиска
     * @var string
     */
    protected $queryString = ['userId' => ['except' => '']];

    public function render(): View
    {
        $consumers = Consumer::query()
            ->when($this->userId, fn($q) => $q->where('user_id', $this->userId))
            ->orderBy($this->sortField, $this->sortType)
            ->paginate(10);

        return view('livewire.consumers.index-table', compact('consumers'));
    }
}
