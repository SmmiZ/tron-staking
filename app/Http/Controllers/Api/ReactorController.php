<?php

namespace App\Http\Controllers\Api;

use App\Enums\ReactorTypes;
use App\Events\ReactorStartEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReactorRequest;
use App\Http\Resources\Reactor\{ReactorCollection, ReactorResource};
use App\Models\Reactor;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\DB;

class ReactorController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Reactor::class);
    }

    public function index(Request $request): ReactorCollection
    {
        return new ReactorCollection($request->user()->reactors()->paginate(20));
    }

    public function store(StoreReactorRequest $request): Response
    {
        $data = [];
        $reactor = [
            'user_id' => $request->user()->id,
            'type' => ReactorTypes::standard,
            'active_until' => now()->addYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        for ($i = 0; $i < $request->get('count'); $i++) {
            $data[] = $reactor;
        }
        DB::table('reactors')->insert($data);

        //todo покупка
        event(new ReactorStartEvent($request->user()));

        return response([
            'status' => true,
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
