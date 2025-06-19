<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostLiked extends Notification
{
    use Queueable;

    protected $liker;
    protected $postId;

    public function __construct($liker, $postId)
    {
        $this->liker = $liker;
        $this->postId = $postId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "{$this->liker->username} curtiu seu post.",
            'post_id' => $this->postId,
        ];
    }
}
