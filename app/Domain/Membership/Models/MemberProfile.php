<?php

declare(strict_types=1);

namespace App\Domain\Membership\Models;

use App\Domain\Identity\Models\User;
use Database\Factories\MemberProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The full registration record — Schema §2.3, FR-3.6, FR-5.2.
 */
final class MemberProfile extends Model
{
    /** @use HasFactory<MemberProfileFactory> */
    use HasFactory;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    /**
     * FR-5.2 — a member may edit these. Name, email, category, status,
     * membership number and expiry are NOT here and are not self-editable;
     * changing them requires an admin (Schema §5.3 rule 4, enforced by an
     * allow-list rather than a deny-list).
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'organisation', 'job_title', 'qualifications',
        'ndpc_licence_number', 'state', 'city', 'referral_source',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
