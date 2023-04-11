<?php

namespace App\Http\Livewire\Traits;
use Livewire\WithPagination;

trait Sorter
{
    use WithPagination;

    public string $sortType = 'asc';
    public string $sortField = 'id';

    public function sort($sortField): void
    {
        $this->sortType = $this->sortType == 'desc' ? 'asc' : 'desc';
        $this->sortField = $sortField;
        $this->resetPage();
    }

    public function paginationView()
    {
        return 'livewire.pagination.view';
    }
}
