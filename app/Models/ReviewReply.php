<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReviewReply extends Model
{
    use HasFactory;

    protected $table = 'review_replies';

    protected $fillable = [
        'review_id',
        'user_id',
        'comment',
    ];

    /**
     * The review this reply belongs to
     */
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * The user who wrote the reply
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}