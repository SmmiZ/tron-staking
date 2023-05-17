<?php

namespace App\Http\Resources\Structure;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class LevelResource extends BaseResource
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
            'level' => $this->level,
            'name_ru' => $this->name_ru,
            'name_en' => $this->name_en,
            'conditions' => $this->conditions,
            'alt_conditions' => $this->alt_conditions,
            'line_percents' => $this->line_percents,
            'reward' => $this->reward,
        ];
    }
}
