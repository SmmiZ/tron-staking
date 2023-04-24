<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Stake\StoreStakeRequest;
use App\Http\Resources\Stake\StakeResource;
use App\Models\Stake;
use App\Services\StakeService;
use App\Services\TronApi\Exception\TronException;
use Illuminate\Http\{Request, Response};
use Throwable;

class StakeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Stake::class);
    }

    public function store(StoreStakeRequest $request): Response
    {
        try {
            $newStakeId = (new StakeService($request->user()->wallet))->stake($request->validated('trx_amount'));
        } catch (Throwable $e) {
            return response([
                'status' => false,
                'error' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine(),
                'errors' => (object)[]
            ]);
        }

        return response([
            'status' => true,
            'data' => [
                'id' => $newStakeId,
            ],
        ]);
    }

    public function show(Stake $stake): StakeResource
    {
        return new StakeResource($stake);
    }

    /**
     * @throws TronException
     */
    public function destroy(Request $request, Stake $stake): Response
    {
        return response([
            'status' => (new StakeService($request->user()->wallet))->unstake($stake),
        ]);
    }
}
