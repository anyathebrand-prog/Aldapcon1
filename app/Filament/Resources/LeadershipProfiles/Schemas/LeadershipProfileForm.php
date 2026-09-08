<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadershipProfiles\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Leadership profile form — FR-1.3, AC-F1, screen D-13.
 *
 * AC-F1: "An admin can add, edit, reorder and remove a leadership profile,
 * and the change appears publicly without a deploy."
 *
 * Photographs are added in Phase 16 alongside the image derivative pipeline;
 * until then the public page renders a neutral initial-based placeholder
 * rather than a broken image (App Flow P-03). Alt text is NOT NULL at the
 * database level, so the upload cannot ship without it (NFR 6.5).
 */
final class LeadershipProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(150),

            TextInput::make('position')
                ->required()
                ->maxLength(150)
                ->helperText('For example: President, Secretary General.'),

            Textarea::make('bio')
                ->rows(4)
                ->columnSpanFull()
                ->helperText('A short paragraph. Long biographies are truncated with an expand control on the public page.'),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->helperText('Lower numbers appear first.'),

            Toggle::make('is_published')
                ->default(true)
                ->helperText('The leadership page stays unlinked from the navigation until at least one profile is published.'),
        ])->columns(2);
    }
}
