<?php

namespace App\Console\Commands;

use App\Services\Address;
use IEXBase\TronAPI\Exception\TronException;
use IEXBase\TronAPI\Provider\HttpProvider;
use IEXBase\TronAPI\Tron;
use Illuminate\Console\Command;

class Vote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staking:vote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Найти и проголосовать за топового SR';

    public function handle()
    {
        try {
            $trxWallet = config('app.trx_wallet');
            $fullNode = $solidityNode = $eventServer = new HttpProvider('https://api.trongrid.io');
            $tron = new Tron($fullNode, $solidityNode, $eventServer, null, null, config('app.private_key'));

            $resources = $tron->getAccountResources($trxWallet);
            $availableVotes = $resources['tronPowerLimit'] - $resources['tronPowerUsed'];

            if ($availableVotes <= 0) {
                exit('No available votes');
            }

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
            $this->error('Something went wrong');

            exit($e->getMessage());
        }

        $this->info('The command was successful!');
    }
}
