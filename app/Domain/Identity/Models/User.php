<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * The authentication identity — Schema §2.1.
 *
 * Every member has one; not every user is a member. Admins and publishers
 * have a user row and no membership, which is why membership lives in its own
 * table (Phase 6) rather than as columns here.
 *
 * @property int $id
 * @property string $uuid
 * @property string $full_name
 * @property string $email
 * @property string $phone
 * @property bool $is_active
 */
final class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    /**
     * Mass assignment is guarded by an ALLOW-LIST, never a deny-list
     * (plan §2 rule 7, Schema §5.3 rule 4).
     *
     * The realistic path to a member self-promoting to admin is a forgotten
     * $guarded entry, so the list below contains only what a user may ever
     * legitimately set about themselves. Notably absent: is_active, and
     * anything to do with two-factor or verification state.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            // Encrypted at rest (Schema §2.1). A database backup must not
            // hand somebody a working second factor.
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
        ];
    }

    /**
     * Deactivated accounts cannot log in (Schema §2.1). Checked at
     * authentication in Phase 4; exposed here so the rule has one home.
     */
    public function canLogIn(): bool
    {
        return $this->is_active && $this->deleted_at === null;
    }

    /**
     * FR-4.3 — two-factor is mandatory for Super Admin and Admin.
     * Publisher is not included: FR-9.7 grants Publisher no member, payment or
     * certificate data, so the second factor protects nothing they can reach.
     */
    public function requiresTwoFactor(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * The outermost gate on the Filament panel — FR-9.7, AC-F9.
     *
     * A member must not reach the admin panel at all: not a 403 inside it, not
     * an empty dashboard, but refused at the door. Per-resource permissions
     * still apply beneath this; this only decides who sees a panel.
     *
     * Publisher is included because FR-9.7 gives them content and events to
     * manage, and the panel is where that happens (plan C-6).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canLogIn()
            && $this->hasAnyRole(['super_admin', 'admin', 'publisher']);
    }

    /**
     * The name Filament shows in the panel.
     *
     * Filament looks for a `name` attribute by default; Schema §2.1 calls the
     * column `full_name`, so the contract is implemented rather than the
     * column renamed. Without this the panel raises a TypeError on every page
     * — getUserName() is declared to return string and finds null.
     */
    public function getFilamentName(): string
    {
        return $this->full_name;
    }
}
