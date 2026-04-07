<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements Auditable, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use AuditableTrait, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'google_id',
        'phone',
        'locale_preferred',
        'terms_accepted_at',
        'email_verified_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * @var list<string>
     */
    protected $auditExclude = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Events this user is rostered on (volunteer opportunities / attendance).
     *
     * @return BelongsToMany<Event, User>
     */
    public function rosteredEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_volunteers')
            ->withTimestamps();
    }

    /**
     * Bookmarked volunteer opportunities (see event_saves pivot).
     *
     * @return BelongsToMany<Event, User>
     */
    public function savedEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_saves')
            ->withTimestamps();
    }

    /**
     * @return HasMany<EventApplication, User>
     */
    public function eventApplications(): HasMany
    {
        return $this->hasMany(EventApplication::class);
    }

    /**
     * @return HasMany<Attendance, User>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * @return HasOne<VolunteerProfile, User>
     */
    public function volunteerProfile(): HasOne
    {
        return $this->hasOne(VolunteerProfile::class);
    }

    /**
     * @return BelongsTo<Organization, User>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Whether this volunteer has filled the minimum profile required to apply or join rosters.
     */
    public function hasMinimumVolunteerProfileForCommitments(): bool
    {
        if (! $this->hasRole('volunteer')) {
            return true;
        }

        $profile = $this->volunteerProfile;

        return $profile !== null && $profile->meetsCommitmentMinimum();
    }
}
