<?php

declare(strict_types=1);

namespace App\Filament\Resources\MembershipCategories\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * Membership category form — FR-2.1, FR-2.2, AC-F2, screen D-06.
 *
 * FR-2.2: "Categories and fees must be editable by an admin without a code
 * change." This form is that requirement.
 *
 * ── Money ────────────────────────────────────────────────────────────────
 *
 * Stored as BIGINT kobo (plan §2 rule 1). An administrator thinks in naira,
 * so the field takes naira and converts on save. The conversion lives here,
 * once, rather than in every place a fee is read.
 *
 * AC-F2: editing a fee changes the price for NEW payments only. Historical
 * payments snapshot their own amount, so nothing here can alter a receipt
 * issued last year — but the warning is shown anyway, because an administrator
 * about to change a price deserves to know which way that works.
 */
final class MembershipCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(120)
                ->unique(ignoreRecord: true)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $operation, $state, callable $set): void {
                    if ($operation === 'create') {
                        $set('slug', Str::slug((string) $state));
                    }
                }),

            TextInput::make('slug')
                ->required()
                ->maxLength(140)
                ->unique(ignoreRecord: true)
                ->helperText('Used in the join address: /join/{slug}'),

            Select::make('applicant_type')
                ->required()
                ->default('individual')
                ->options([
                    'individual' => 'Individual',
                    'organisation' => 'Organisation',
                ])
                ->helperText('Whether this category is for a person or a licensed organisation.'),

            TextInput::make('annual_fee_naira')
                ->label('Annual fee (₦)')
                ->required()
                ->numeric()
                ->minValue(0)
                ->prefix('₦')
                // Money is kobo in the database and naira in this field. The
                // conversion is here so no other code has to know about it.
                ->formatStateUsing(fn (?int $state): ?int => $state === null ? null : intdiv($state, 100))
                ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100))
                ->afterStateHydrated(function (TextInput $component, $state, $record): void {
                    if ($record !== null) {
                        $component->state(intdiv((int) $record->annual_fee_kobo, 100));
                    }
                })
                ->helperText('Changing this affects new payments only. Receipts already issued keep the amount that was charged.'),

            TextInput::make('number_prefix')
                ->required()
                ->maxLength(12)
                ->unique(ignoreRecord: true)
                ->placeholder('DPCO')
                ->helperText('Appears in every membership number for this category, e.g. DPCO-2026-00034. Cannot be reused by another category.'),

            Textarea::make('eligibility')
                ->required()
                ->rows(3)
                ->columnSpanFull()
                ->helperText('Who qualifies. Shown on the public membership page.'),

            Repeater::make('benefits')
                ->simple(TextInput::make('benefit')->required()->maxLength(200))
                ->columnSpanFull()
                ->helperText('One per line, shown as a list under the category.'),

            Toggle::make('requires_verification')
                ->label('Requires certificate verification')
                ->helperText(
                    'When on, applicants must supply an NDPC licence number and certificate, '
                    .'and an administrator must approve before any membership is created. '
                    .'When off, membership activates immediately on registration.'
                ),

            Toggle::make('is_active')
                ->default(true)
                ->helperText('Turning this off removes the category from the join flow. Existing members keep their membership.'),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->helperText('Lower numbers appear first.'),
        ])->columns(2);
    }
}
