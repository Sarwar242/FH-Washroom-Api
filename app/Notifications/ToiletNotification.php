<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ToiletNotification extends Notification
{
    use Queueable;

    private $message;
    private $type;
    private $toiletId;

    public function __construct($message, $type, $toiletId)
    {
        $this->message = $message;
        $this->type = $type;
        $this->toiletId = $toiletId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'toilet_id' => $this->toiletId
        ];
    }
}
