<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\User;
use App\Notifications\NewReviewNotification;

class ReviewObserver
{
    public function created(Review $review): void
    {
        // Notify the provider about a new review
        $provider = User::find($review->provider_id);
        if ($provider) {
            $review->loadMissing('taker');
            $provider->notify(new NewReviewNotification($review));
        }
    }
}
