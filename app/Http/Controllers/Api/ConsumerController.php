<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Consumer;
use App\Enums\InternalTxTypes;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, Response};
use App\Http\Requests\Api\PayConsumerRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Api\StoreConsumerRequest;
use App\Http\Resources\Consumer\ConsumerResource;
use App\Http\Resources\Consumer\ConsumerCollection;

class ConsumerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Consumer::class);
    }

    public function index(Request $request): ConsumerCollection
    {
        return new ConsumerCollection($request->user()->consumers()->paginate(20));
    }

    public function store(StoreConsumerRequest $request): ConsumerResource
    {
        return new ConsumerResource($request->user()->consumers()->create(
            $request->validated()
        ));
    }

    public function show(Consumer $consumer): ConsumerResource
    {
        return new ConsumerResource($consumer);
    }

    public function destroy(Consumer $consumer): Response
    {
        return response([
            'status' => $consumer->delete(),
        ]);
    }


    /**
     * 
     * Оплачивает созданную заявку
     * 
     * @var PayConsumerRequest $request
     * @return Response
     */
    public function payConsumer(PayConsumerRequest $request): Response
    {
        $consumers = Consumer::whereIn('id', $request->consumers)->get();
        $balance = $request->user()->internalTxs()->balance()->value('amount');
        $toPay = ($consumers->sum('resource_amount') * config('app.energy_price') / 1_000_000) * $request->days;
        if ($balance < $toPay) {
            throw ValidationException::withMessages(['balance' => 'Not enough money']);
        }
        DB::beginTransaction();
        try {
            $request->user()->internalTxs()->create([
                'amount' => $toPay,
                'received' => $toPay,
                'type' => InternalTxTypes::fromName('consumer'),
            ]);
            foreach ($consumers as $consumer) {
                $consumer->order()->create([
                    'resource_amount' => $consumer->resource_amount,
                    'period' => $request->days
                ]);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            info($e);
            return new Response([
                'error' => $e->getMessage(),
                'errors' => ['exception' => $e->getMessage(),],
                'status' => false,
            ], 422);
        }

        return response([
            'status' => true,
        ]);
    }
}
