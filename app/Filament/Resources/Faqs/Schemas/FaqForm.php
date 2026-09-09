<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * FAQ form — FR-1.1, FR-1.6, screen D-14.
 *
 * Answers are plain text rather than rich text on purpose: they are rendered
 * inside a disclosure on the public page, they feed the site-wide search
 * index, and an editor pasting styled HTML from a document is the usual way a
 * FAQ page stops matching the rest of the site.
 */
final class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('question')
                ->required()
                ->maxLength(400)
                ->columnSpanFull(),

            Textarea::make('answer')
                ->required()
                ->rows(5)
                ->columnSpanFull(),

            TextInput::make('group')
                ->maxLength(80)
                ->placeholder('Membership')
                ->helperText('Questions are shown together under this heading. Leave blank for General.'),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->helperText('Lower numbers appear first.'),

            Toggle::make('is_published')->default(true),
        ])->columns(2);
    }
}
