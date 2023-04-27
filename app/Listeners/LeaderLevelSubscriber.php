<?php

namespace App\Listeners;

use App\Events\{NewStakeEvent, ReactorPurchasedEvent};
use App\Models\{LeaderLevel, User};
use App\Services\LeaderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

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

        $leaders = User::withCount('reactors')->whereIn('id', $leaderIds)->orderByDesc('id')->get();
        $levels = LeaderLevel::where('level', '>', 0)->orderByDesc('level')->get();

        foreach ($leaders as $i => $leader) {
            if ($i > 19) {
                break;
            }

            (new LeaderService($leader))->updateLevel($levels);
        }
    }
}
