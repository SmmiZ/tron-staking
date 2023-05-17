<?php

namespace App\Http\Livewire\Withdrawals;

use App\Http\Livewire\Traits\Sorter;
use App\Models\Withdrawal;
use Livewire\Component;

class IndexTable extends Component
{
    use Sorter;

    public $status;
    public $userId;

    /**
     * Забираем из get-параметров данные для поиска
     * @var string
     */
    protected $queryString = ['userId' => ['except' => '']];

    public function __construct()
    {
        parent::__construct();

        $this->sortField = 'created_at';
        $this->sortType = 'desc';
    }

    public function render()
    {
        $withdrawals = Withdrawal::query()
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->userId, fn($q) => $q->where('user_id', $this->userId))
            ->orderBy($this->sortField, $this->sortType)
            ->paginate(15);

        return view('livewire.withdrawals.index-table', compact('withdrawals'));
    }
}
