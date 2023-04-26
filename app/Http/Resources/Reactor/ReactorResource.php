<?php

namespace App\Http\Resources\Reactor;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class ReactorResource extends BaseResource
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
            'type' => $this->type->value,
            'active_until' => $this->active_until?->format('d-m-Y H:i:s'),
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i:s'),
        ];
    }
}
