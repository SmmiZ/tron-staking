<?php

namespace App\Console\Commands;

use App\Enums\InternalTxTypes;
use App\Jobs\BanUser;
use App\Models\{InternalTx, User};
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Console\Command;
use Illuminate\Support\{Carbon, Sleep};

class SendProfitToUsers extends Command
{
    private Tron $tron;
    private Carbon $now;

    private int $days = 3;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-profit-to-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отправить пользователям их долю прибыли от сервиса';

    /**
     * Execute the console command.
     * @throws TronException
     */
    public function handle()
    {
        $this->tron = new Tron();
        $this->now = now();

        User::with([
            'wallet',
            'executions:id,order_id,user_id,trx_amount' => ['consumer:consumers.id,address'],
            'stakes' => fn($q) => $q->where('trx_amount', '>', 0)->whereDate('available_at', '<=', $this->now->subDays($this->days))
        ])
            ->whereRelation('wallet', 'failed_attempts', '<', 3)
            ->select(['id'])
            ->chunkById(100, function ($users) {
                $profitTxs = [];
                foreach ($users as $user) {
                    $suitableStakes = $user->stakes->filter(fn($stake) => $this->now->diffInDays($stake->available_at) % $this->days == 0);

                    if ($suitableStakes->isEmpty() || !$this->checkDelegatedResources($user)) {
                        continue;
                    }

                    $dailyProfit = $suitableStakes->sum('trx_amount') * (float)(config('app.profit') / 365);
                    $profitTxs[] = [
                        'user_id' => $user->id,
                        'amount' => $dailyProfit * $this->days,
                        'received' => $dailyProfit * $this->days,
                        'type' => InternalTxTypes::stakeProfit,
                        'created_at' => $this->now,
                        'updated_at' => $this->now,
                    ];
                }

                if (!empty($profitTxs)) {
                    InternalTx::query()->insert($profitTxs);
                }
                sleep(1);
            });
    }

    /**
     * @throws TronException
     */
    private function checkDelegatedResources(User $user): bool
    {
        $tronTotal = 0;
        foreach ($user->executions as $execution) {
            $delegatedResources = $this->tron->getDelegatedResources($user->wallet->address, $execution->consumer->address);
            $tronTotal += data_get($delegatedResources,'delegatedResource.0.frozen_balance_for_energy', 0);

            Sleep::for(config('app.sleep_ms'))->milliseconds();
        }

        $result = $user->executions->sum('trx_amount') == ($tronTotal / $this->tron::ONE_SUN);
        BanUser::dispatchIf(!$result, $user->id);

        return $result;
    }
}
