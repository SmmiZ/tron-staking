<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TrxAmountRequest;
use App\Http\Resources\Stake\{StakeCollection, StakeResource};
use App\Models\Stake;
use App\Services\StakeService;
use App\Services\TronApi\Exception\TronException;
use Illuminate\Http\{Request, Response};

class StakeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Stake::class);
    }

    public function index(Request $request): StakeCollection
    {
        return new StakeCollection($request->user()->stakes()->paginate(20));
    }

    /**
     * @throws TronException
     */
    public function store(TrxAmountRequest $request): Response
    {
        if (!$request->user()->wallet) {
            throw new TronException('User has no wallet');
        }

        $status = (new StakeService($request->user()->wallet))->freeze($request->validated('trx_amount'));

        return response([
            'status' => $status,
            'data' => [],
        ]);
    }

    public function show(Request $request, Stake $stake): StakeResource
    {
        return new StakeResource($stake);
    }

    public function destroy(Request $request, Stake $stake): Response
    {
        $unstakeDate = now();
        foreach (now()->toPeriod(now()->addDays(3)) as $date) {
            if ($date->diffInDays($stake->available_at) % 3 == 0) {
                $unstakeDate = $date;
                break;
            }
        }

        return response([
            'status' => $stake->is_closes || $stake->update(['is_closes' => true]),
            'data' => [
                'unstake_date' => $unstakeDate->format('Y-m-d'),
            ],
        ]);
    }
}
