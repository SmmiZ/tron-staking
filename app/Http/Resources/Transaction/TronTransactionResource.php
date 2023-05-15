<?php

namespace App\Http\Resources\Transaction;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class TronTransactionResource extends BaseResource
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
            'from' => $this->from,
            'to' => $this->to,
            'type' => $this->type->translate(),
            'trx_amount' => (float)$this->trx_amount,
            'tx_id' => $this->tx_id,
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}
