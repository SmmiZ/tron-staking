<?php

namespace App\Jobs;

use App\Services\Address;
use App\Services\TronApi\Exception\TronException;
use App\Services\TronApi\Tron;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class VoteSR implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            //todo доработать
            $trxWallet = config('app.trx_wallet');

            $tron = new Tron($trxWallet, config('app.private_key'));
            $resources = $tron->getAccountResources($trxWallet);

            $availableVotes = $resources['tronPowerLimit'] - ($resources['tronPowerUsed'] ?? 0);

            if ($availableVotes <= 0) {
                Log::emergency('No available votes');

                return;
            }

            //todo вынести выбор топового SR и голосование в разные методы
            $maxVoteCount = $srAddress = 0;
            foreach ($tron->listSuperRepresentatives() as $sr) {
                if (isset($sr['voteCount'], $sr['address']) && $sr['voteCount'] > $maxVoteCount) {
                    $maxVoteCount = $sr['voteCount'];
                    $srAddress = $sr['address'];
                }
            }

            $vote = $tron->getManager()->request('wallet/votewitnessaccount', [
                'owner_address' => Address::decode($trxWallet),
                'votes' => [
                    'vote_address' => $srAddress,
                    'vote_count' => $availableVotes,
                ],
            ]);
            $signedTransaction = $tron->signTransaction($vote);
            $tron->sendRawTransaction($signedTransaction);
        } catch (TronException $e) {
            Log::emergency('TronException: ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());

            exit($e->getMessage());
        }
    }
}
