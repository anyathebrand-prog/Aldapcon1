<?php

declare(strict_types=1);

namespace App\Domain\Membership\Models;

use App\Domain\Identity\Models\User;
use Database\Factories\ApplicantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The paid-but-not-yet-member entity — Schema §1.2, §2.3.
 *
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $token_expires_at
 */
final class Applicant extends Model
{
    /** @use HasFactory<ApplicantFactory> */
    use HasFactory;

    /**
     * registration_token_hash is deliberately absent: it is set by the action
     * that issues the token, never by mass assignment from a form.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid', 'category_id', 'full_name', 'email', 'phone',
        'status', 'fee_kobo_at_initiation', 'paid_at',
    ];

    /** @var list<string> */
    protected $hidden = ['registration_token_hash'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'registered_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'token_expires_at' => 'datetime',
            'fee_kobo_at_initiation' => 'integer',
        ];
    }

    /** @return BelongsTo<MembershipCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'category_id');
    }

    /** @return BelongsTo<Membership, $this> */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<MemberDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(MemberDocument::class);
    }

    /**
     * Paid, but registration not yet completed — FR-3.8.
     *
     * The D-04 queue. App Flow D-04: "This is the screen that prevents
     * paid-but-invisible people."
     */
    public function isAwaitingRegistration(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Submitted and waiting on an administrator — change set 01.
     *
     * Holds an account and a profile but NO membership (FR-3.12), which is why
     * the portal must render a pending state rather than an empty dashboard,
     * and why the Membership Record panel is never shown here
     * (UI brief §11 never-19).
     */
    public function isAwaitingVerification(): bool
    {
        return $this->status === 'pending_verification';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
