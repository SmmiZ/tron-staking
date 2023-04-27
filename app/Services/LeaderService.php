<?php

namespace App\Services;

use App\Enums\InternalTxTypes;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeaderService
{
    /** Понижение уровня */
    private bool $withDowngrade = false;

    public function __construct(private readonly User $leader)
    {
        //
    }

    public function withDowngrade(): self
    {
        $this->withDowngrade = true;

        return $this;
    }

    /**
     * Пересмотр лидерского уровня пользователя
     *
     * @param Collection $levels
     * @return void
     */
    public function updateLevel(Collection $levels): void
    {
        $mainLinesUsers = $this->leader->mainInvitedUsers()->withCount(['reactors'])->withSum('stake', 'trx_amount')->get();

        $reactorsCount = $mainLinesUsers->sum('reactors_count');
        $trxSum = $mainLinesUsers->sum('stake_sum_trx_amount');

        $firstLineLeaders = User::query()
            ->whereIn('id', $mainLinesUsers->pluck('id')->toArray())
            ->select(['leader_level', DB::raw('count(*) as total')])
            ->where('leader_level', '>', 5)
            ->groupBy('leader_level')
            ->pluck('total', 'leader_level')
            ->toArray();

        $newLevel = $levels->filter(fn($level) => ($level->alt_conditions && $level->alt_conditions->trx <= $trxSum)
            || ($level->conditions->reactors && $level->conditions->reactors <= $reactorsCount
                && ($level->conditions->trx <= $trxSum
                    || ($level->conditions->leaders->level <= array_keys($firstLineLeaders)
                        && $level->conditions->leaders->number <= array_values($firstLineLeaders))
                )
            )
        )->first()->level ?? 0;

        //todo
        // Если уровень был поднят по alternative_conditions, надо это где-то отметить и проверять его каждые день т.к. стейк может закончиться.

        switch (true) {
            case $newLevel < $this->leader->leader_level:
                $this->withDowngrade
                    ? $this->leader->update(['leader_level' => $newLevel])
                    : $this->leader->downgrade()->firstOrCreate();
                break;
            case $newLevel > $this->leader->leader_level:
                $this->leader->downgrade()->delete();
                $this->leader->update(['leader_level' => $newLevel]);

                $type = InternalTxTypes::from('levelReward' . $newLevel);
                $this->leader->internalTxs()->where('type', $type)->existsOr(
                    fn() => $this->leader->internalTxs()->create([
                        'type' => $type,
                        'amount' => $levels->where('level', $newLevel)->value('reward'),
                    ]));
                break;
            default:
                $this->leader->downgrade()->delete();
                break;
        }
    }
}
