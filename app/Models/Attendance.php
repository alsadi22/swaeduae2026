<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    public const STATE_PENDING = 'pending';

    public const STATE_CHECKED_IN = 'checked_in';

    public const STATE_CHECKED_OUT = 'checked_out';

    public const STATE_NO_SHOW = 'no_show';

    public const STATE_INCOMPLETE = 'incomplete';

    protected $fillable = [
        'event_id',
        'user_id',
        'state',
        'checked_in_at',
        'checked_out_at',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_accuracy_meters',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_accuracy_meters',
        'suspicion_flags',
        'minutes_worked',
        'minutes_adjustment',
        'minutes_adjustment_note',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'check_in_latitude' => 'float',
            'check_in_longitude' => 'float',
            'check_in_accuracy_meters' => 'float',
            'check_out_latitude' => 'float',
            'check_out_longitude' => 'float',
            'check_out_accuracy_meters' => 'float',
            'suspicion_flags' => 'array',
            'minutes_worked' => 'integer',
            'minutes_adjustment' => 'integer',
        ];
    }

    /**
     * Recorded time plus admin adjustment, non-negative; null unless checked out with minutes_worked.
     */
    public function verifiedMinutes(): ?int
    {
        if ($this->state !== self::STATE_CHECKED_OUT || $this->minutes_worked === null) {
            return null;
        }

        $base = (int) $this->minutes_worked;
        $adj = (int) ($this->minutes_adjustment ?? 0);

        return max(0, $base + $adj);
    }

    public static function localizedStateLabel(?string $state): string
    {
        if ($state === null || $state === '') {
            return '';
        }

        return match ($state) {
            self::STATE_PENDING => __('Attendance state pending'),
            self::STATE_CHECKED_IN => __('Attendance state checked_in'),
            self::STATE_CHECKED_OUT => __('Attendance state checked_out'),
            self::STATE_NO_SHOW => __('Attendance state no_show'),
            self::STATE_INCOMPLETE => __('Attendance state incomplete'),
            default => $state,
        };
    }

    public function hasOpenDispute(): bool
    {
        return $this->disputes()
            ->where('status', Dispute::STATUS_OPEN)
            ->exists();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    /**
     * Attendance rows that still carry at least one suspicion flag after check-in/out.
     */
    public function scopeWithNonEmptySuspicionFlags(Builder $query): Builder
    {
        $driver = $query->getConnection()->getDriverName();

        return match ($driver) {
            'pgsql' => $query->whereRaw('(suspicion_flags IS NOT NULL AND jsonb_array_length(suspicion_flags::jsonb) > 0)'),
            'mysql', 'mariadb' => $query->whereRaw('JSON_LENGTH(COALESCE(suspicion_flags, JSON_ARRAY())) > 0'),
            default => $query->whereNotNull('suspicion_flags')->where('suspicion_flags', '!=', '[]'),
        };
    }
}
