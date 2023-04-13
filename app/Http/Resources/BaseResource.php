<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class BaseResource extends JsonResource
{
    public function with(Request $request): array
    {
        $data = ['status' => true];

        if (config('app.debug')) {
            $data += [
                '***DEBUG***' => [
                    'request' => $request->all(),
                    'queries' => DB::getQueryLog()
                ]
            ];
        }

        return $data ?? [];
    }
}
