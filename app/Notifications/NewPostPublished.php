<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewPostPublished extends Notification
{
    public function __construct(private Post $post) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'post.published',
            'title' => 'New post in '.$this->post->type->label(),
            'body' => Str::limit($this->post->content, 90),
            'author' => $this->post->author->name,
            'icon' => $this->post->type->icon(),
            'url' => route($this->post->type->indexRoute()),
        ];
    }
}
