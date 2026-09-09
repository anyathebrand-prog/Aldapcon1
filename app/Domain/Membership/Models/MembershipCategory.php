<?php

declare(strict_types=1);

namespace App\Domain\Membership\Models;

use Database\Factories\MembershipCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A membership category — Schema §2.3, FR-2.1, FR-2.2.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $number_prefix
 * @property int $annual_fee_kobo
 * @property bool $requires_verification
 * @property bool $is_active
 */
final class MembershipCategory extends Model
{
    /** @use HasFactory<MembershipCategoryFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name', 'slug', 'applicant_type', 'eligibility', 'benefits',
        'annual_fee_kobo', 'currency', 'requires_verification',
        'is_active', 'number_prefix', 'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'annual_fee_kobo' => 'integer',
            'requires_verification' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Every category gets a number counter, always.
     *
     * A category without one cannot issue membership numbers, and the failure
     * would surface at activation — after somebody had paid. Created here so
     * the two cannot drift apart.
     */
    protected static function booted(): void
    {
        self::created(function (self $category): void {
            $category->counter()->firstOrCreate([], ['last_number' => 0]);
        });
    }

    /** @return HasMany<MembershipNumberCounter, $this> */
    public function counter(): HasMany
    {
        return $this->hasMany(MembershipNumberCounter::class, 'category_id');
    }

    /** @return HasMany<Membership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'category_id');
    }

    /**
     * The fee in naira, for display. Never used for arithmetic — money is
     * kobo everywhere it is calculated (plan §2 rule 1).
     */
    public function feeInNaira(): string
    {
        return '₦'.number_format($this->annual_fee_kobo / 100);
    }
}
