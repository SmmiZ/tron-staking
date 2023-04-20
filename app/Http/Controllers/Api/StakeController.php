<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Stake\{StoreStakeRequest, UpdateStakeRequest};
use App\Http\Resources\Stake\{StakeCollection, StakeResource};
use App\Models\Stake;
use App\Services\StakeService;
use Illuminate\Http\{Request, Response};
use Throwable;

class StakeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Stake::class);
    }

    public function index(Request $request): StakeCollection
    {
        return new StakeCollection($request->user()->stakes);
    }

    public function store(StoreStakeRequest $request): Response
    {
        try {
            $newStakeId = (new StakeService($request->user()->wallet))->store($request->validated('trx_amount'));
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

    public function update(UpdateStakeRequest $request, Stake $stake): Response
    {
        return response([
            'status' => $stake->update($request->validated()),
            'data' => (object)[],
        ]);
    }

    public function destroy(Stake $stake): Response
    {
        return response([
            'status' => $stake->delete(),
        ]);
    }
}
