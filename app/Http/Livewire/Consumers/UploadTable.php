<?php

namespace App\Http\Livewire\Consumers;

use App\Http\Livewire\Traits\Sorter;
use App\Imports\ConsumersImport;
use App\Models\Consumer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\{Component, WithFileUploads};
use Maatwebsite\Excel\Facades\Excel;

class UploadTable extends Component
{
    use Sorter;
    use WithFileUploads;

    public $userId;
    public $file;
    public $fileName;
    public $url;

    public function mount()
    {
        $this->fileName = 'user' . $this->userId . '_consumers.xlsx';
        $this->url = storage_path('app/excel-consumers/' . $this->fileName);
    }

    public function save()
    {
        $this->validate([
            'file' => 'mimes:xlsx,xls|max:1024',
        ]);

        $this->file->storeAs('excel-consumers', $this->fileName);
        $this->dispatchBrowserEvent('refresh-page');
    }

    public function render(): View
    {
        if (Storage::fileExists('excel-consumers/' . $this->fileName)) {
            $fileData = (new ConsumersImport($this->userId))->toCollection($this->url)->flatten();

            $existingAddresses = Consumer::whereIn('address', $fileData->toArray())->pluck('address');
            $consumers = $fileData->map(fn($address) => (object)[
                'address' => $address,
                'exists' => $existingAddresses->contains($address),
            ]);
        }

        //todo pagination
        return view('livewire.consumers.upload-table', [
            'consumers' => collect($consumers ?? [])
        ]);
    }

    public function updateConsumers()
    {
        Excel::import(new ConsumersImport($this->userId), $this->url);
        session()->flash('message', 'User consumers successfully updated.');

        Storage::delete('excel-consumers/' . $this->fileName);
    }

    public function cancel()
    {
        Storage::delete('excel-consumers/' . $this->fileName);

        session()->flash('message', 'User consumers file successfully deleted.');
    }
}
