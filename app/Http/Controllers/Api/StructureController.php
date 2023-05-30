<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Structure\{LevelCollection, PartnerResource};
use App\Models\{LeaderLevel, User};
use Illuminate\Http\{Request, Response};

class StructureController extends Controller
{
    public function levels(): LevelCollection
    {
        return new LevelCollection(LeaderLevel::all());
    }

    public function partners(Request $request): Response
    {
        $result = [];
        $lines = $request->user()->lines()->get();

        foreach ($lines as $line) {
            $linePartners = User::with([
                'level:level,name_ru,name_en',
                'stakes:user_id,trx_amount'
            ])
                ->whereIn('id', $line->ids)
                ->get(['id', 'name', 'leader_level']);

            $result[] = [
                'line' => $line->line,
                'users' => PartnerResource::collection($linePartners),
            ];
        }

        return response([
            'status' => true,
            'data' => $result,
        ]);
    }
}
