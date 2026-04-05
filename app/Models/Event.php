<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'organization_id',
        'capacity',
        'application_required',
        'title_en',
        'title_ar',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'geofence_strict',
        'min_gps_accuracy_meters',
        'checkin_window_starts_at',
        'checkin_window_ends_at',
        'event_starts_at',
        'event_ends_at',
        'checkout_grace_minutes_after_event',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'geofence_strict' => 'boolean',
            'application_required' => 'boolean',
            'checkin_window_starts_at' => 'datetime',
            'checkin_window_ends_at' => 'datetime',
            'event_starts_at' => 'datetime',
            'event_ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->uuid)) {
                $event->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function volunteers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_volunteers')
            ->withTimestamps();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(EventApplication::class);
    }

    public function applicationForUser(User $user): ?EventApplication
    {
        return $this->applications()->where('user_id', $user->id)->first();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function checkinAttempts(): HasMany
    {
        return $this->hasMany(CheckinAttempt::class);
    }

    public function userIsOnRoster(User $user): bool
    {
        return $this->volunteers()->whereKey($user->getKey())->exists();
    }

    public function titleForLocale(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'ar' && filled($this->title_ar)) {
            return $this->title_ar;
        }

        return $this->title_en;
    }

    /**
     * Whether the volunteer roster still accepts new members (ignores auth; uses live count).
     */
    public function rosterAcceptsNewVolunteers(): bool
    {
        if ($this->capacity === null) {
            return true;
        }

        return $this->volunteers()->count() < $this->capacity;
    }

    /**
     * Whether the organization portal should refuse deleting this event (admin delete is unchanged).
     * Uses {@see withCount()} columns when both are present to avoid extra queries on list pages.
     */
    public function blocksOrganizationPortalDeletion(): bool
    {
        $volunteersCount = $this->attributes['volunteers_count'] ?? null;
        $attendancesCount = $this->attributes['attendances_count'] ?? null;
        if ($volunteersCount !== null && $attendancesCount !== null) {
            return (int) $volunteersCount > 0 || (int) $attendancesCount > 0;
        }

        return $this->volunteers()->exists() || $this->attendances()->exists();
    }
}
