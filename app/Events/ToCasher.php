<?php

namespace App\Events;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Pusher\Pusher;

class ToCasher implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function broadcastOn()
    {
        return [
            new Channel('Casher'),
        ];
    }
    public function broadcastWith()
    {
        return [
            'Casher' => new OrderResource($this->order)
        ];
    }
    public function broadcastAs()
    {
        return 'ToCasher';
    }

    public function broadcastWhen()
    {
        return true;
    }

    public function broadcastAfterCommit()
    {
        $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), [
            'cluster' => env('PUSHER_APP_CLUSTER'),
        ]);
        $pusher->trigger('Casher', 'ToCasher', ['order_id' => $this->order->id]);
    }


}
