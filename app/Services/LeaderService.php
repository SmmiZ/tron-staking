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

    private User $leader;

    public function __construct(User $leader)
    {
        $this->leader = $leader->load(['leader']);
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
        $newLevel = $this->calculateNewLevel($levels);

        switch (true) {
            case $newLevel < $this->leader->leader_level:
                $this->withDowngrade
                    ? $this->leader->update(['leader_level' => $newLevel])
                    : $this->leader->downgrade()->firstOrCreate();
                break;
            case $newLevel > $this->leader->leader_level:
                $this->leader->downgrade()->delete();
                $this->leader->update(['leader_level' => $newLevel]);

                $type = InternalTxTypes::fromName('levelReward' . $newLevel);
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

        if ($nextLeader = $this->leader->leader) {
            (new self($nextLeader))->updateLevel($levels);
        }
    }

    private function calculateNewLevel(Collection $levels): int
    {
        $threeLinesIds = $this->leader->lines()->whereIn('line', [1, 2, 3])->get(['ids', 'line']);

        $threeLinesUsers = User::query()
            ->withCount(['reactors'])
            ->withSum('stakes', 'trx_amount')
            ->whereIn('id', $threeLinesIds->pluck('ids')->collapse()->toArray())->get();

        $reactorsCount = $threeLinesUsers->sum('reactors_count');
        $trxSum = $threeLinesUsers->sum('stakes_sum_trx_amount');

        $firstLineLeaders = User::whereIn('id', $threeLinesIds->where('line', 1)->pluck('ids')->collapse()->toArray())
            ->select(['leader_level', DB::raw('count(*) as total')])
            ->where('leader_level', '>', 5)
            ->groupBy('leader_level')
            ->pluck('total', 'leader_level')
            ->toArray();

        return $levels->filter(fn($level) => ($level->alt_conditions && $level->alt_conditions->trx <= $trxSum)
            || ($level->conditions->reactors && $level->conditions->reactors <= $reactorsCount
                && ($level->conditions->trx <= $trxSum
                    || ($level->conditions->leaders->level <= array_keys($firstLineLeaders)
                        && $level->conditions->leaders->number <= array_values($firstLineLeaders))
                )
            )
        )->first()->level ?? 0;
    }
}
