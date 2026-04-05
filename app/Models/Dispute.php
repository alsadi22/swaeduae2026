<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = [
        'attendance_id',
        'opened_by_user_id',
        'status',
        'description',
        'resolved_at',
        'resolved_by_user_id',
        'resolution_note',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
