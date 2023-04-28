<?php

namespace App\Listeners;

use App\Enums\InternalTxTypes;
use App\Events\ProfitReceivedEvent;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProfitListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProfitReceivedEvent $event): void
    {
        $user = $event->tx->user;
        $txAmount = $event->tx->received;

        $leaderIds = explode('/', trim($user->linear_path, '/'));
        array_shift($leaderIds);

        $leaders = User::with(['level'])->withCount(['reactors'])->whereIn('id', $leaderIds)->orderByDesc('id')->get();

        $paidPercent = 0;
        foreach ($leaders as $i => $leader) {
            if ($i > 19 || $paidPercent >= 10) break;

            if (!$levelPercent = $leader->level->line_percents[$i + 1] ?? 0) {
                continue;
            }

            $hasReactors = $leader->reactors_count > 0;
            $type = InternalTxTypes::fromName('lineBonus' . $i + 1);

            if ($i < 3) {
                $leader->internalTxs()->create([
                    'amount' => $amount = $txAmount * $levelPercent / 100,
                    'received' => $hasReactors ? $amount : 0,
                    'type' => $type,
                ]);
                continue;
            }

            if (!$leader->leader_level || ($leaderPercent = $levelPercent - $paidPercent) <= 0) continue;

            $leader->internalTxs()->create([
                'amount' => $amount = $txAmount * $leaderPercent / 100,
                'received' => $hasReactors ? $amount : 0,
                'type' => $type,
            ]);

            $paidPercent += $leaderPercent;
        }
    }
}
