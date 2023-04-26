<?php

namespace App\Listeners;

use App\Events\{NewStakeEvent, ReactorPurchasedEvent};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Event;

class LeaderLevelSubscriber implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

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
        //todo пересчет, обновление и т.д.
    }
}
