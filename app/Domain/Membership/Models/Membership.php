<?php

declare(strict_types=1);

namespace App\Domain\Membership\Models;

use App\Domain\Identity\Models\User;
use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A membership — Schema §2.3, FR-5.1, FR-6.1 to FR-6.6.
 *
 * A row exists here only for a real member. A pending applicant has none
 * (audit C-14).
 *
 * @property string $membership_number
 * @property string $status
 * @property \Illuminate\Support\Carbon $joined_at
 * @property \Illuminate\Support\Carbon $expires_at
 */
final class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use HasFactory;

    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'uuid', 'user_id', 'category_id', 'membership_number',
        'status', 'joined_at', 'expires_at', 'last_renewed_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_renewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<MembershipCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'category_id');
    }

    /**
     * FR-6.5 — an expired member keeps their account but loses member
     * privileges: member event pricing and member-only announcements.
     *
     * Expiry restricts what they can do, never whether they can log in
     * (App Flow A-01). Locking them out would remove the one screen that
     * offers renewal.
     */
    public function hasMemberPrivileges(): bool
    {
        return in_array($this->status, ['active', 'expiring_soon'], true);
    }

    /**
     * The status this membership should hold today — FR-6.2.
     *
     * Derived here and stored by the daily job, rather than computed on every
     * read: the dashboard counts in FR-9.1 have to be indexable, and a
     * computed status cannot be.
     */
    public function derivedStatus(int $expiringSoonDays = 30): string
    {
        // Suspended is an administrative decision, not a date. The scheduler
        // must never overwrite it.
        if ($this->status === 'suspended') {
            return 'suspended';
        }

        if ($this->expires_at->isPast()) {
            return 'expired';
        }

        // Signed, and measured FROM now: Carbon returns a negative figure when
        // the target is in the future, so an unsigned comparison would make
        // every membership look like it expires today.
        $daysRemaining = now()->diffInDays($this->expires_at, false);

        return $daysRemaining <= $expiringSoonDays
            ? 'expiring_soon'
            : 'active';
    }
}
