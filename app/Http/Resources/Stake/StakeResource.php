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
            'amount' => $this->amount,
            'days' => $this->days,
            'status' => $this->status,
            'deleted_at' => $this->deleted_at?->format('d-m-Y H:i:s'),
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}