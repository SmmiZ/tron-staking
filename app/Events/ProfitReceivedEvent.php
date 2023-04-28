<?php

namespace App\Events;

use App\Models\InternalTx;
use Illuminate\Support\Facades\Event;
use Illuminate\Broadcasting\{InteractsWithSockets, PrivateChannel};
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfitReceivedEvent extends Event
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public InternalTx $tx)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
