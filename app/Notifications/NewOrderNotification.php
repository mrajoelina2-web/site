<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    public function __construct(public Order $order, public User $buyer)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'buyer_name' => $this->buyer->name,
            'buyer_email' => $this->buyer->email,
            'nb_articles' => $this->order->nb_articles,
            'total' => $this->order->total,
        ];
    }
}