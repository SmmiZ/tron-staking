<?php

namespace App\Imports;

use App\Models\Consumer;
use Maatwebsite\Excel\Concerns\{Importable, ToModel, WithUpserts};

class ConsumersImport implements ToModel, WithUpserts
{
    use Importable;

    public function __construct(private readonly int $userId)
    {
        //
    }

    public function uniqueBy(): string
    {
        return 'address';
    }

    /**
     * Формирование модели для записи в БД
     *
     * @param array $row
     * @return Consumer
     */
    public function model(array $row): Consumer
    {
        return new Consumer([
            'user_id' => $this->userId,
            'name' => 'upload_' . $row[0],
            'address' => $row[0],
        ]);
    }
}
