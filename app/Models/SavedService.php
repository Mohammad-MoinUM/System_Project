<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedService extends Model
{
    protected $fillable = [
        'taker_id',
        'service_id',
    ];

    public function taker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taker_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
