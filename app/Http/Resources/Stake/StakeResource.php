<?php

namespace App\Http\Resources\Stake;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class StakeResource extends BaseResource
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
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'available_at' => $this->available_at?->format('d-m-Y H:i:s'),
        ];
    }
}
