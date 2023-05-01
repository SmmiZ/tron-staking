<?php

namespace App\Http\Resources\Transaction;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class InternalTransactionResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'received' => $this->received,
            'type' => $this->type->translate(),
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
        ];
    }
}
