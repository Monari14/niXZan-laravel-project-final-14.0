<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostCommented extends Notification
{
    use Queueable;

    protected $commenter;
    protected $postId;

    public function __construct($commenter, $postId)
    {
        $this->commenter = $commenter;
        $this->postId = $postId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "{$this->commenter->username} comentou seu post.",
            'post_id' => $this->postId,
        ];
    }
}
