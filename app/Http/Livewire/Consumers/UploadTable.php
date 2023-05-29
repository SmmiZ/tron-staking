<?php

namespace App\Http\Livewire\Consumers;

use App\Console\Commands\DeleteRemovedConsumersOrders;
use App\Http\Livewire\Traits\Sorter;
use App\Imports\ConsumersImport;
use App\Models\Consumer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Artisan, Storage};
use Livewire\{Component, WithFileUploads};

class UploadTable extends Component
{
    use Sorter;
    use WithFileUploads;

    public $userId;
    public $file;
    public $fileName;
    public $fileUrl;

    public function mount()
    {
        $this->fileName = 'user' . $this->userId . '_consumers.xlsx';
        $this->fileUrl = storage_path('app/excel-consumers/' . $this->fileName);
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
        $consumers = Storage::fileExists('excel-consumers/' . $this->fileName)
            ? $this->getConsumerList()
            : collect();

        //todo pagination
        return view('livewire.consumers.upload-table', compact('consumers'));
    }

    public function updateConsumers()
    {
        $consumers = $this->getConsumerList();
        $toRemove = $consumers->where('remove', true)->pluck('address')->toArray();
        $toKeep = $consumers->where('remove', false);

        //Удаляем
        Consumer::where('user_id', $this->userId)->whereIn('address', $toRemove)->delete();
        //Восстанавливаем
        Consumer::onlyTrashed()->where('user_id', $this->userId)->whereIn('address', $toKeep->pluck('address')->toArray())->restore();
        //Создаем/обновляем
        Consumer::where('user_id', $this->userId)->upsert($toKeep->map(fn($consumer) => [
            'user_id' => $this->userId,
            'name' => 'upload_' . $consumer->address,
            'address' => $consumer->address,
        ])->toArray(), ['address'], ['name']);
        //Запускаем команду очистки заказов
        Artisan::call(DeleteRemovedConsumersOrders::class);

        session()->flash('message', 'User consumers successfully updated.');

        Storage::delete('excel-consumers/' . $this->fileName);
    }

    public function getConsumerList(): Collection
    {
        $importer = new ConsumersImport($this->userId);

        $dbConsumers = $importer->getCurrentConsumers();
        $fileConsumers = $importer->toCollection($this->fileUrl)->flatten();
        $allConsumers = $fileConsumers->merge($dbConsumers)->unique()->values();

        return $allConsumers->map(fn($address) => (object)[
            'address' => $address,
            'add' => $fileConsumers->contains($address) && $dbConsumers->doesntContain($address),
            'remove' => $fileConsumers->doesntContain($address),
        ]);
    }

    public function cancel()
    {
        Storage::delete('excel-consumers/' . $this->fileName);

        session()->flash('message', 'User consumers file successfully deleted.');
    }
}
