<?php

namespace App\Console\Commands;

use App\Models\{LeaderLevel, UserDowngrade};
use App\Services\LeaderService;
use Illuminate\Console\Command;

class LeaderLevelDowngrade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:leader-level-downgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Понижение лидерского уровня пользователей';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $levels = LeaderLevel::where('level', '>', 0)->orderByDesc('level')->get();

        UserDowngrade::with(['user'])
            ->where('created_at', '<=', now()->subDays(7))
            ->orderBy('id')
            ->chunk(100, function ($downgradeList) use ($levels) {
                foreach ($downgradeList as $userDowngrade) {
                    (new LeaderService($userDowngrade->user))->withDowngrade()->updateLevel($levels);

                    $userDowngrade->delete();
                    //todo запуск пересчета уровня верхним юзерам
                }
            });
    }
}
