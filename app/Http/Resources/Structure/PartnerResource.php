<?php

namespace App\Http\Resources\Structure;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class PartnerResource extends BaseResource
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
            'name' => $this->name,
            'photo' => $this->photo,
            'level_name' => $this->level->name,
            'stake' => $this->stake->trx_amount ?? 0,
        ];
    }
}
