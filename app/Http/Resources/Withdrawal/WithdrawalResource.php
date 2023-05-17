<?php

namespace App\Http\Resources\Withdrawal;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class WithdrawalResource extends BaseResource
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
            'trx_amount' => $this->trx_amount,
            'status' => $this->status->translate(),
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}
