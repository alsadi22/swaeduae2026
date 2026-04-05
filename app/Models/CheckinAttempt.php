<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinAttempt extends Model
{
    public const TYPE_CHECK_IN = 'check_in';

    public const TYPE_CHECK_OUT = 'check_out';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'user_id',
        'attempt_type',
        'latitude',
        'longitude',
        'accuracy_meters',
        'distance_meters',
        'outcome',
        'rejection_reason',
        'flags',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy_meters' => 'float',
            'distance_meters' => 'float',
            'flags' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
