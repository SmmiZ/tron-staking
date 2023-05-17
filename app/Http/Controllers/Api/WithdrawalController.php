<?php

namespace App\Http\Controllers\Api;

use App\Enums\Statuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TrxAmountRequest;
use App\Http\Resources\Withdrawal\{WithdrawalCollection, WithdrawalResource};
use App\Models\Withdrawal;
use Illuminate\Http\{Request, Response};

class WithdrawalController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Withdrawal::class);
    }

    public function index(Request $request): WithdrawalCollection
    {
        return new WithdrawalCollection($request->user()->withdrawals()->paginate(20));
    }

    public function store(TrxAmountRequest $request): WithdrawalResource
    {
        $withdrawal = $request->user()->withdrawals()->create($request->validated() + ['status' => Statuses::new]);

        return new WithdrawalResource($withdrawal);
    }

    public function show(Withdrawal $withdrawal): WithdrawalResource
    {
        return new WithdrawalResource($withdrawal);
    }

    public function destroy(Withdrawal $withdrawal): Response
    {
        return response([
            'status' => $withdrawal->delete(),
        ]);
    }
}
