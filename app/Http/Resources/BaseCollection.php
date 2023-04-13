<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

class BaseCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'data' => $this->collection,
        ];
    }

    public function with(Request $request): array
    {
        if (config('app.debug')) {
            $data = [
                '***DEBUG***' => [
                    'request' => $request->all(),
                    'queries' => DB::getQueryLog()
                ]
            ];
        }

        return $data ?? [];
    }
}
