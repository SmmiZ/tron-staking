<?php

namespace App\Http\Controllers\Api;

use App\Enums\InternalTxTypes;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Api\{PayConsumerRequest, StoreConsumerRequest};
use App\Http\Resources\Consumer\{ConsumerCollection, ConsumerResource};
use App\Models\Consumer;
use App\Services\TronApi\Tron;
use Exception;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

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
     * Оплатить свои потребительские аккаунты
     *
     * @return Response
     * @throws ValidationException
     * @var PayConsumerRequest $request
     */
    public function payConsumer(PayConsumerRequest $request): Response
    {
        $consumers = Consumer::whereIn('id', $request->consumers)->get();
        $balance = $request->user()->internalTxs()->balance()->value('amount');
        $toPay = ($consumers->sum('resource_amount') * config('app.energy_price') / Tron::ONE_SUN) * $request->days;

        if ($balance < $toPay) {
            throw ValidationException::withMessages(['balance' => 'Not enough money']);
        }

        DB::beginTransaction();
        try {
            $request->user()->internalTxs()->create([
                'amount' => $toPay,
                'received' => $toPay,
                'type' => InternalTxTypes::consumer,
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
            Log::critical('Error while paying consumer', ['error' => $e]);

            return new Response([
                'status' => false,
                'error' => $e->getMessage(),
                'errors' => (object)['exception' => $e->getMessage()],
            ], 422);
        }

        return response([
            'status' => true,
        ]);
    }
}
