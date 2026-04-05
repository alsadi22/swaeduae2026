<?php

namespace App\Models;

use Database\Factories\VolunteerProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolunteerProfile extends Model
{
    /** @use HasFactory<VolunteerProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'skills',
        'availability',
        'emergency_contact_name',
        'emergency_contact_phone',
        'photo_path',
        'emirates_id_masked',
        'notification_email_opt_in',
    ];

    protected function casts(): array
    {
        return [
            'notification_email_opt_in' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Minimum fields required before applying to opportunities or joining an open roster.
     */
    public function meetsCommitmentMinimum(): bool
    {
        $bio = trim((string) $this->bio);

        return strlen($bio) >= 20
            && filled($this->emergency_contact_name)
            && filled($this->emergency_contact_phone);
    }
}
