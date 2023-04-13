<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Stake\{StoreStakeRequest, UpdateStakeRequest};
use App\Http\Resources\{Stake\StakeCollection, Stake\StakeResource};
use App\Models\Stake;
use Illuminate\Http\Response;

class StakeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Stake::class);
    }

    public function index(): StakeCollection
    {
        return new StakeCollection(Stake::all());
    }

    public function store(StoreStakeRequest $request): Response
    {
        $newStake = $request->user()->stakes()->create($request->validated());

        return response([
            'status' => true,
            'data' => [
                'id' => $newStake->id,
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
