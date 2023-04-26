<?php

namespace App\Http\Controllers\Api;

use App\Events\ReactorPurchasedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReactorRequest;
use App\Http\Resources\Reactor\{ReactorCollection, ReactorResource};
use App\Models\Reactor;
use Illuminate\Http\{Request, Response};

class ReactorController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Reactor::class);
    }

    public function index(Request $request): ReactorCollection
    {
        return new ReactorCollection($request->user()->reactors);
    }

    public function store(StoreReactorRequest $request): Response
    {
        $reactors = Reactor::factory($request->count)->create([
            'user_id' => $request->user()->id,
        ]);

        //todo покупка
        event(new ReactorPurchasedEvent());

        return response([
            'status' => true,
            'data' => [
                'ids' => $reactors->pluck('id')
            ],
        ]);
    }

    public function show(Reactor $reactor): ReactorResource
    {
        return new ReactorResource($reactor);
    }

    public function destroy(Reactor $reactor): Response
    {
        return response([
            'status' => $reactor->delete(),
        ]);
    }
}
