<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderPortfolioItem extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'before_image_path',
        'after_image_path',
        'cover_image_path',
        'job_date',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'job_date' => 'date',
            'is_public' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
