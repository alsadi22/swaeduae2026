<?php

namespace App\Models;

use Database\Factories\OrganizationInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationInvitation extends Model
{
    /** @use HasFactory<OrganizationInvitationFactory> */
    use HasFactory;

    public const ROLE_MANAGER = 'org-manager';

    public const ROLE_COORDINATOR = 'org-coordinator';

    public const ROLE_VIEWER = 'org-viewer';

    /** @var list<string> */
    public const INVITABLE_ROLES = [
        self::ROLE_MANAGER,
        self::ROLE_COORDINATOR,
        self::ROLE_VIEWER,
    ];

    protected $fillable = [
        'organization_id',
        'email',
        'role',
        'token_hash',
        'invited_by_user_id',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Organization, OrganizationInvitation>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<User, OrganizationInvitation>
     */
    public function invitedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return ! $this->isAccepted() && ! $this->isExpired();
    }
}
