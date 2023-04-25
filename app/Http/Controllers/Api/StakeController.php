<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Stake\StoreStakeRequest;
use App\Http\Resources\Stake\StakeResource;
use App\Models\{OrderExecutor, Stake};
use App\Services\StakeService;
use App\Services\TronApi\Exception\TronException;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\DB;

class StakeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Stake::class);
    }

    /**
     * @throws TronException
     */
    public function stake(StoreStakeRequest $request): Response
    {
        $status = (new StakeService($request->user()->wallet))->stake($request->validated('trx_amount'));

        return response([
            'status' => $status,
            'data' => [],
        ]);
    }

    public function show(Stake $stake): StakeResource
    {
        return new StakeResource($stake); //todo изменить ответ
    }

    public function getAvailableUnfreezeTrxAmount(Request $request): Response
    {
        $total = $request->user()->stake->trx_amount;
        $locked = OrderExecutor::where('user_id', $request->user()->id)->where('unlocked_at', '>', now())->sum('trx_amount');

        return response([
            'status' => true,
            'data' => [
                'trx_amount' => (int)($total - $locked),
            ],
        ]);
    }

    /**
     * @throws TronException
     */
    public function unstake(StoreStakeRequest $request): Response
    {
        $trxAmount = $request->validated('trx_amount');
        $status = (new StakeService($request->user()->wallet))->unstake($trxAmount);

        if ($status) {
            $request->user()->stake()->update(['trx_amount' => DB::raw('trx_amount - ' . $trxAmount)]);
        }

        return response([
            'status' => $status,
            'data' => [],
        ]);
    }
}
