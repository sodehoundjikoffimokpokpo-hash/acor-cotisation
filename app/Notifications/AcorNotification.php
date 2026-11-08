<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AcorNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $type = 'info')
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // On ajoute 'database' ici pour stocker la notif dans la table 'notifications'
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     * (C'est ce qui sera stocké dans la colonne 'data' de la table)
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
        ];
    }
}
