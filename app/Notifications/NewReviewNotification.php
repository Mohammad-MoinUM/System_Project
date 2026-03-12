<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification
{
    use Queueable;

    public function __construct(public Review $review) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $reviewerName = $this->review->taker?->name ?? 'A customer';

        return [
            'title' => 'New Review Received',
            'message' => "{$reviewerName} left a {$this->review->rating}-star review.",
            'icon' => 'star',
            'review_id' => $this->review->id,
            'type' => 'new_review',
        ];
    }
}
