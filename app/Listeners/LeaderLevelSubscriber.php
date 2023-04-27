<?php

namespace App\Listeners;

use App\Events\{NewStakeEvent, ReactorPurchasedEvent};
use App\Models\{LeaderLevel, User};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB, Event};

class LeaderLevelSubscriber implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    private Collection $levels;

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            NewStakeEvent::class => 'handle',
            ReactorPurchasedEvent::class => 'handle',
        ];
    }

    /**
     * Handle user login events.
     */
    public function handle(Event $event): void
    {
        $leaderIds = explode('/', trim($event->user->linear_path, '/'));
        array_shift($leaderIds);

        $leaders = User::whereIn('id', $leaderIds)->orderByDesc('id')->get();
        $this->levels = LeaderLevel::where('level', '>', 0)->orderByDesc('level')->get();

        foreach ($leaders as $i => $leader) {
            if ($i > 19) {
                break;
            }

            $this->updateLeaderLevel($leader);

            //todo
            // Если за поднятие уровня есть награда, то вручаем её только один раз, когда впервые был поднят уровень. user_balances отдельным типом записываем награду и уровень (чтобы можно было в следующие разы проверять получал ли юзер за этот уровень или нет)
            // Если уровень был поднят по alternative_conditions, надо это где-то отметить и проверять его каждые день т.к. стейк может закончиться.
        }
    }

    /**
     * Пересмотр лидерского уровня пользователя
     *
     * @param User $leader
     * @return void
     */
    private function updateLeaderLevel(User $leader): void
    {
        $mainLinesUsers = $leader->mainInvitedUsers()->withCount(['reactors'])->withSum('stake', 'trx_amount')->get();

        $reactorsCount = $mainLinesUsers->sum('reactors_count');
        $trxSum = $mainLinesUsers->sum('stake_sum_trx_amount');

        $firstLineLeaders = User::query()
            ->whereIn('id', $mainLinesUsers->pluck('id')->toArray())
            ->select(['leader_level', DB::raw('count(*) as total')])
            ->where('leader_level', '>', 5)
            ->groupBy('leader_level')
            ->pluck('total', 'leader_level')
            ->toArray();

        $newLevel = $this->levels->filter(fn($level) => ($level->alt_conditions && $level->alt_conditions->trx <= $trxSum)
            || ($level->conditions->reactors && $level->conditions->reactors <= $reactorsCount
                && ($level->conditions->trx <= $trxSum
                    || ($level->conditions->leaders->level <= array_keys($firstLineLeaders)
                        && $level->conditions->leaders->number <= array_values($firstLineLeaders))
                )
            )
        )->first()->level ?? 0;

        if ($newLevel !== $leader->leader_level) {
            $leader->update(['leader_level' => $newLevel]);
        }
    }
}
