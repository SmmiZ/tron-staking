<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InviteRequest;
use App\Http\Resources\Structure\{LevelCollection, PartnerResource};
use App\Mail\InviteCode;
use App\Models\{LeaderLevel, User};
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Mail;
use Throwable;

class StructureController extends Controller
{
    public function invite(InviteRequest $request): Response
    {
        try {
            Mail::to($request->email)->send(new InviteCode($request->user()));
        } catch (Throwable $e) {
            return response([
                'status' => false,
                'error' => $e->getMessage(),
                'errors' => (object)[],
            ], 500);
        }

        return response([
            'status' => true,
            'data' => [],
        ]);
    }

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
